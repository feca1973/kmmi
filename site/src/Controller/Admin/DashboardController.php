<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

/**
 * The dashboard names every CRUD controller in its menu.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        // As in the legacy admin, the article list opens after the login.
        return $this->redirectToRoute('admin_article_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<b>kmmi</b><span style="font-weight: 300;">admin</span>')
            ->disableDarkMode()
            ->setDefaultColorScheme('light');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addAssetMapperEntry('admin')->addCssFile('admin/admin.css');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()->setPaginatorPageSize(15);
    }

    /**
     * The legacy menu items in the legacy order; an item appears here when its CRUD is built.
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkTo(RegionsCrudController::class, 'Régiók', 'fa fa-road');
        yield MenuItem::linkTo(CitiesCrudController::class, 'Települések', 'fa fa-house');
        yield MenuItem::linkTo(ArticleCrudController::class, 'Tartalmak', 'fa fa-list');
        yield MenuItem::linkTo(ArticleTypeCrudController::class, 'Tartalomcsoportok', 'fa fa-rectangle-list');
        yield MenuItem::linkTo(EventCrudController::class, 'Események', 'fa fa-calendar');
        yield MenuItem::linkTo(EventPlacesCrudController::class, 'Esemény helyszínek', 'fa fa-calendar');
        yield MenuItem::linkTo(OrganizationCrudController::class, 'Szervezetek', 'fa fa-sitemap');
        yield MenuItem::linkTo(MonumentCrudController::class, 'Emlékhelyek', 'fa-brands fa-fort-awesome');
        yield MenuItem::linkTo(MonumentTypeCrudController::class, 'Emlékhely típusok', 'fa fa-compress');
        yield MenuItem::linkTo(MonumentSubmitterCrudController::class, 'Emlékhely készíttető', 'fa fa-user-xmark');
        yield MenuItem::linkTo(GalleryCrudController::class, 'Galériák', 'fa fa-image');
        yield MenuItem::linkTo(MediaCrudController::class, 'Média', 'fa fa-play');
        yield MenuItem::linkTo(SliderCrudController::class, 'Slider', 'fa fa-sliders');
        yield MenuItem::linkTo(DocCrudController::class, 'Dokumentumok', 'fa fa-file-lines');
        yield MenuItem::linkTo(BookCategoryCrudController::class, 'Könyvtártípusok', 'fa fa-book');
        yield MenuItem::linkTo(MenuCrudController::class, 'Menü', 'fa fa-bars');
        yield MenuItem::linkTo(UserCrudController::class, 'Felhasználók', 'fa fa-user');
        yield MenuItem::linkTo(UniqueGroupCrudController::class, 'Egyedi tartalmak', 'fa fa-thumbtack');
    }
}
