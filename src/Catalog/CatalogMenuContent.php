<?php

namespace App\Catalog;

use App\Repository\ProductRepository;
use App\Repository\SiteSettingRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Builds the storefront catalog mega-menu content: admin-editable heading /
 * button text (stored in site_settings) plus a strip of random promo products
 * pulled live from the catalog.
 */
final class CatalogMenuContent
{
    private const PROMO_COUNT = 3;

    /** Editable text keys and their defaults. */
    private const DEFAULTS = [
        'catalog_menu_heading' => 'Mövsümün seçimləri',
        'catalog_menu_subheading' => 'Eviniz üçün ən yaxşı mebel həlləri',
        'catalog_menu_button_label' => 'Bütün kataloqa bax',
        'catalog_menu_button_href' => '/catalog',
    ];

    public function __construct(
        private readonly SiteSettingRepository $settings,
        private readonly ProductRepository $products,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Full public payload for the storefront (editable text + random promos).
     *
     * @return array<string, mixed>
     */
    public function publicPayload(): array
    {
        return [
            ...$this->editable(),
            'promos' => array_map(
                static fn (array $p): array => [
                    'id' => $p['id'],
                    'name' => $p['name'],
                    'image' => $p['image'],
                    'price' => $p['price'],
                    'href' => '/product/'.$p['id'],
                ],
                $this->products->findRandomForPromo(self::PROMO_COUNT),
            ),
        ];
    }

    /**
     * The editable text values (stored value or default), keyed without the
     * "catalog_menu_" prefix for a compact API shape.
     *
     * @return array<string, string>
     */
    public function editable(): array
    {
        $stored = $this->settings->map();

        return [
            'heading' => $stored['catalog_menu_heading'] ?? self::DEFAULTS['catalog_menu_heading'],
            'subheading' => $stored['catalog_menu_subheading'] ?? self::DEFAULTS['catalog_menu_subheading'],
            'buttonLabel' => $stored['catalog_menu_button_label'] ?? self::DEFAULTS['catalog_menu_button_label'],
            'buttonHref' => $stored['catalog_menu_button_href'] ?? self::DEFAULTS['catalog_menu_button_href'],
        ];
    }

    /**
     * Persists the editable text values from an admin payload.
     */
    public function save(string $heading, string $subheading, string $buttonLabel, string $buttonHref): void
    {
        $this->settings->set('catalog_menu_heading', $heading);
        $this->settings->set('catalog_menu_subheading', $subheading);
        $this->settings->set('catalog_menu_button_label', $buttonLabel);
        $this->settings->set('catalog_menu_button_href', $buttonHref);
        $this->em->flush();
    }
}
