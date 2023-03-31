<?php

namespace App\Form\Admin\Field;

use App\Entity\Media;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Contracts\Translation\TranslatableInterface;

final class ThumbnailPhotoField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_AUTOCOMPLETE = 'autocomplete';
    public const OPTION_EMBEDDED_CRUD_FORM_CONTROLLER = 'crudControllerFqcn';
    /** @deprecated since easycorp/easyadmin-bundle 4.4.3 use AssociationField::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER */
    public const OPTION_CRUD_CONTROLLER = self::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER;
    public const OPTION_WIDGET = 'widget';
    public const OPTION_QUERY_BUILDER_CALLABLE = 'queryBuilderCallable';
    /** @internal this option is intended for internal use only */
    public const OPTION_RELATED_URL = 'relatedUrl';
    /** @internal this option is intended for internal use only */
    public const OPTION_DOCTRINE_ASSOCIATION_TYPE = 'associationType';

    public const WIDGET_AUTOCOMPLETE = 'autocomplete';
    public const WIDGET_NATIVE = 'native';

    /** @internal this option is intended for internal use only */
    public const PARAM_AUTOCOMPLETE_CONTEXT = 'autocompleteContext';

    /** @internal this option is intended for internal use only */
    public const OPTION_RENDER_AS_EMBEDDED_FORM = 'renderAsEmbeddedForm';

    public const OPTION_EMBEDDED_CRUD_FORM_NEW_PAGE_NAME = 'crudNewPageName';
    public const OPTION_EMBEDDED_CRUD_FORM_EDIT_PAGE_NAME = 'crudEditPageName';

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        $package = new Package(new JsonManifestVersionStrategy(getcwd() . '/build/admin/manifest.json'));

        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplateName('crud/field/association')
            ->setFormType(EntityType::class)
            ->setFormTypeOptions([
                'class' => Media::class,
            ])
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                $qb = $entityRepository->createQueryBuilder('m');
                $qb->join('m.mediaCategories', 'mc')
                    ->where($qb->expr()->eq('mc.slug', ':slug'))
                    ->orderBy('m.id', 'DESC')
                    ->setParameter(':slug', 'miniatura');

                return $qb;
            })
            ->addCssClass('field-association field-thumbnail-photo')
            ->addCssFiles(Asset::new($package->getUrl('build/admin/thumbnailPhotoField.css'))->onlyOnForms())
            ->addJsFiles(Asset::new($package->getUrl('build/admin/thumbnailPhotoField.js'))->onlyOnForms())
            ->setDefaultColumns('col-md-7 col-xxl-6')
            ->setCustomOption(self::OPTION_AUTOCOMPLETE, false)
            ->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER, null)
            ->setCustomOption(self::OPTION_WIDGET, self::WIDGET_AUTOCOMPLETE)
            ->setCustomOption(self::OPTION_QUERY_BUILDER_CALLABLE, null)
            ->setCustomOption(self::OPTION_RELATED_URL, null)
            ->setCustomOption(self::OPTION_DOCTRINE_ASSOCIATION_TYPE, null)
            ->setCustomOption(self::OPTION_RENDER_AS_EMBEDDED_FORM, false)
            ->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_NEW_PAGE_NAME, null)
            ->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_EDIT_PAGE_NAME, null);
    }

    public function autocomplete(): self
    {
        $this->setCustomOption(self::OPTION_AUTOCOMPLETE, true);

        return $this;
    }

    public function renderAsNativeWidget(bool $asNative = true): self
    {
        $this->setCustomOption(self::OPTION_WIDGET, $asNative ? self::WIDGET_NATIVE : self::WIDGET_AUTOCOMPLETE);

        return $this;
    }

    public function setCrudController(string $crudControllerFqcn): self
    {
        $this->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER, $crudControllerFqcn);

        return $this;
    }

    public function setQueryBuilder(\Closure $queryBuilderCallable): self
    {
        $this->setCustomOption(self::OPTION_QUERY_BUILDER_CALLABLE, $queryBuilderCallable);

        return $this;
    }

    public function renderAsEmbeddedForm(
        ?string $crudControllerFqcn = null,
        ?string $crudNewPageName = null,
        ?string $crudEditPageName = null,
    ): self {
        $this->setCustomOption(self::OPTION_RENDER_AS_EMBEDDED_FORM, true);
        $this->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_CONTROLLER, $crudControllerFqcn);
        $this->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_NEW_PAGE_NAME, $crudNewPageName);
        $this->setCustomOption(self::OPTION_EMBEDDED_CRUD_FORM_EDIT_PAGE_NAME, $crudEditPageName);

        return $this;
    }
}
