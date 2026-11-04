<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * The legacy `roles` column holds a PHP-serialized array (or a comma separated list).
 *
 * The $platform parameters are required by the DBAL Type interface, so PHPMD's unused parameter rule is suppressed.
 *
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
final class LegacyRolesType extends Type
{
    public const NAME = 'array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return serialize($value);
        }

        if (is_string($value)) {
            return $value;
        }

        throw new InvalidArgumentException(
            sprintf('Expected roles as array or string, got %s.', get_debug_type($value))
        );
    }

    /**
     * unserialize() has no non-throwing mode: invalid input is expected here (comma separated roles),
     * so its warning is silenced on purpose.
     *
     * @SuppressWarnings("PHPMD.ErrorControlOperator")
     *
     * @return array<int|string, mixed>
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $unserialized = @unserialize($value, ['allowed_classes' => false]);
            if ($unserialized !== false || $value === 'b:0;') {
                return is_array($unserialized) ? $unserialized : [];
            }

            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
