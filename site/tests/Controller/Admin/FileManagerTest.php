<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The file manager is for admins only, and only accepts the allowed file types (decided from the content).
 *
 * The upload tests run against a real web server (PHP's built-in one): elFinder reads the PHP superglobals
 * ($_FILES, $_POST), which the in-process test client does not fill.
 */
/**
 * @SuppressWarnings("PHPMD.ErrorControlOperator")
 */
final class FileManagerTest extends WebTestCase
{
    use ResetDatabase;

    private string $uploadDir;
    private ?Process $server = null;
    private string $baseUrl = '';

    protected function setUp(): void
    {
        $this->uploadDir = dirname(__DIR__, 3) . '/var/test-uploads';
        (new Filesystem())->remove($this->uploadDir);
        (new Filesystem())->mkdir([$this->uploadDir . '/images', $this->uploadDir . '/attachments']);
    }

    protected function tearDown(): void
    {
        $this->server?->stop();
        (new Filesystem())->remove($this->uploadDir);
        parent::tearDown();
    }

    public function testTheFileManagerIsClosedForAnonymousVisitors(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/efconnect/images?cmd=open&init=1&target=');
        self::assertResponseRedirects('/admin/login');

        $client->request('GET', '/admin/elfinder/images');
        self::assertResponseRedirects('/admin/login');
    }

    public function testAnImageCanBeUploaded(): void
    {
        $browser = $this->loggedInBrowser();

        $reply = $this->upload($browser, 'images', 'photo.png', self::pngBytes());

        self::assertEmpty($reply['error'] ?? null, json_encode($reply));
        self::assertCount(1, $reply['added'] ?? [], json_encode($reply));
        self::assertFileExists($this->uploadDir . '/images/photo.png');
    }

    public function testAPdfCanBeUploadedAsAttachment(): void
    {
        $browser = $this->loggedInBrowser();

        $pdf = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n";
        $reply = $this->upload($browser, 'attachments', 'document.pdf', $pdf);

        self::assertEmpty($reply['error'] ?? null, json_encode($reply));
        self::assertFileExists($this->uploadDir . '/attachments/document.pdf');
    }

    /**
     * @return iterable<string, array{string, string, string}> instance, file name, content
     */
    public static function refusedUploadProvider(): iterable
    {
        yield 'php code with an image name' => ['images', 'shell.png', '<?php echo "hi";'];
        yield 'php file name with image content' => ['images', 'shell.php', self::pngBytes()];
        yield 'double extension php' => ['images', 'shell.php.png', self::pngBytes()];
        yield 'html page' => ['images', 'page.html', '<html><script>alert(1)</script></html>'];
        yield 'svg image' => ['images', 'drawing.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'];
        yield 'htaccess' => ['images', '.htaccess', 'AddType application/x-httpd-php .png'];
        yield 'executable with a pdf name' => ['attachments', 'tool.pdf', "MZ\x90\x00\x03\x00\x00\x00"];
        yield 'html as attachment' => ['attachments', 'page.html', '<html></html>'];
    }

    #[DataProvider('refusedUploadProvider')]
    public function testDangerousUploadsAreRefused(string $instance, string $name, string $content): void
    {
        $browser = $this->loggedInBrowser();

        // A harmless upload first, to be sure the upload itself works in this test setup.
        $okName = $instance === 'images' ? 'ok.png' : 'ok.pdf';
        $okContent = $instance === 'images' ? self::pngBytes() : "%PDF-1.4\n%%EOF\n";
        $control = $this->upload($browser, $instance, $okName, $okContent);
        self::assertEmpty($control['error'] ?? null, 'Control upload: ' . json_encode($control));

        $reply = $this->upload($browser, $instance, $name, $content);

        // elFinder reports a refused file as a warning and does not list it among the added files.
        self::assertSame([], $reply['added'] ?? null, 'The upload must be refused: ' . json_encode($reply));
        self::assertNotEmpty($reply['warning'] ?? $reply['error'] ?? null, json_encode($reply));
        self::assertFileDoesNotExist($this->uploadDir . '/' . $instance . '/' . $name);
        self::assertSame([$okName], array_map('basename', glob($this->uploadDir . '/' . $instance . '/*') ?: []));
    }

    /**
     * @return array<string, mixed>
     */
    private function upload(HttpBrowser $browser, string $instance, string $name, string $content): array
    {
        $root = $this->connector($browser, $instance, ['cmd' => 'open', 'init' => 1, 'target' => '', 'tree' => 1]);
        $temporary = sys_get_temp_dir() . '/' . bin2hex(random_bytes(6)) . '-' . $name;
        file_put_contents($temporary, $content);

        $browser->request(
            'POST',
            $this->baseUrl . '/admin/efconnect/' . $instance,
            ['cmd' => 'upload', 'target' => $root['cwd']['hash']],
            ['upload' => [['tmp_name' => $temporary, 'name' => $name, 'type' => 'application/octet-stream',
                'size' => strlen($content), 'error' => 0]]]
        );
        @unlink($temporary);

        return json_decode((string) $browser->getResponse()->getContent(), true) ?? [];
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function connector(HttpBrowser $browser, string $instance, array $query): array
    {
        $browser->request('GET', $this->baseUrl . '/admin/efconnect/' . $instance . '?' . http_build_query($query));

        return json_decode((string) $browser->getResponse()->getContent(), true) ?? [];
    }

    private function loggedInBrowser(): HttpBrowser
    {
        $email = 'fm-' . bin2hex(random_bytes(4)) . '@example.test';
        $password = 'correct horse battery';
        $user = (new User())->setEmail($email)->setName('FM Admin')->setEnabled(true)->setRoles(['ROLE_ADMIN']);
        $user->setPassword(
            static::getContainer()->get('security.user_password_hasher')->hashPassword($user, $password)
        );
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->startServer();
        $browser = new HttpBrowser(HttpClient::create());
        $crawler = $browser->request('GET', $this->baseUrl . '/admin/login');
        $browser->request('POST', $this->baseUrl . '/admin/login_check', [
            '_username' => $email,
            '_password' => $password,
            '_csrf_token' => $crawler->filter('input[name=_csrf_token]')->attr('value'),
        ]);
        self::assertSame(200, $browser->getInternalResponse()->getStatusCode(), 'Login failed.');

        return $browser;
    }

    private function startServer(): void
    {
        $port = $this->freePort();
        $this->baseUrl = 'http://127.0.0.1:' . $port;
        $root = dirname(__DIR__, 3);
        $this->server = new Process(
            ['php', '-S', '127.0.0.1:' . $port, '-t', 'public', 'public/index.php'],
            $root,
            ['APP_ENV' => 'test', 'APP_DEBUG' => '0']
        );
        $this->server->start();

        for ($attempt = 0; $attempt < 50; ++$attempt) {
            if (@fsockopen('127.0.0.1', $port)) {
                return;
            }
            usleep(100_000);
        }
        self::fail('The test web server did not start.');
    }

    private function freePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0') ?: self::fail('No free port.');
        $port = (int) explode(':', (string) stream_socket_get_name($socket, false))[1];
        fclose($socket);

        return $port;
    }

    private static function pngBytes(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
        );
    }
}
