<?php

namespace App\Controller\Admin;

use App\Entity\Menu\Menu;
use App\Form\Type\MenuItemsFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * The menus of the public site ("Főmenu - HU" is the main menu): the items as a tree of three levels, their order and
 * their targets, as in the legacy admin. The menus themselves are created in the database, the admin edits their items.
 *
 * @extends LegacyCrudController<Menu>
 */
final class MenuCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Menü';
    protected const LABEL_PLURAL = 'Menük';
    protected const INDEX_TITLE = 'Menük';
    protected const EDIT_TITLE = 'Menü szerkesztése';

    public static function getEntityFqcn(): string
    {
        return Menu::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)->addFormTheme('admin/form/menu_tree.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE);
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('title', 'Menü');
    }

    protected function formFields(): iterable
    {
        yield Field::new('menuItems', 'Menü elemek')->setFormType(MenuItemsFormType::class)->setRequired(false);
    }
}
