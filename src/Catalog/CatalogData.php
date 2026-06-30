<?php

namespace App\Catalog;

/**
 * Seed data for the storefront catalog, transcribed from the SvelteKit
 * frontend's mock data so the API serves the same products the UI was built
 * against. Consumed by the app:catalog:seed command.
 *
 * Product on-sale / new / stock flags are derived from the 1-based position to
 * reproduce the frontend's toProduct() logic exactly.
 */
final class CatalogData
{
    /**
     * Category name => image. Order is the display order.
     *
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'Yumşaq mebel' => 'https://embawood.az/image/catalog/Yumusaq%20divanlar/liberia_künc%201310x911_2%20(3).png',
            'Yataq mebeli' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/Siena%20yataq/siena%20yataq%20desti_carpayi.png',
            'Qonaq otağı' => 'https://embawood.az/image/catalog/new/SKy%20qonaq%20desti/sky_qonaq%20dəsti_komod.png',
            'Masa və stullar' => 'https://embawood.az/image/catalog/Stullar/Cross/cross_stul_1.png',
            'Uşaq və gənc' => 'https://embawood.az/image/catalog/Usaq%20və%20genc/Angel/angel_yataq_carpayi.png',
            'Döşəklər' => 'https://embawood.az/image/catalog/Matrass/Classy/classy_160x200.png',
            'Paltar dolabları' => 'https://embawood.az/image/catalog/Bedroom/Yildiz/trumo.png',
            'Komod və güzgülər' => 'https://embawood.az/image/catalog/Bedroom/Tiffany/tiffany_yataq_trumo.png',
            'Künc divanlar' => 'https://embawood.az/image/catalog/Yeni%20divanlar/borneo/borneo2.png',
            'Ofis mebeli' => 'https://embawood.az/image/catalog/Stullar/Cross/cross_stul_1.png',
        ];
    }

    /**
     * Curated collections shown on the home page (featured), with tagline/image.
     * Other collections are created automatically from the products that use
     * them.
     *
     * @return list<array{name: string, tagline: string, image: string}>
     */
    public static function featuredCollections(): array
    {
        return [
            ['name' => 'Lena', 'tagline' => 'Rahat qonaq otağı', 'image' => 'https://embawood.az/image/catalog/Sofa%20set/Lena/lena%203%20yerli_2.png'],
            ['name' => 'Siena', 'tagline' => 'Zərif yataq dəstləri', 'image' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/Siena%20yataq/siena%20yataq%20desti_carpayi.png'],
            ['name' => 'Borneo', 'tagline' => 'Premium salon seriyası', 'image' => 'https://embawood.az/image/catalog/Yeni%20divanlar/borneo/borneo2.png'],
            ['name' => 'Portofino', 'tagline' => 'Minimal yataq otağı', 'image' => 'https://embawood.az/image/catalog/Bedroom/Portofino/tumba_1.png'],
            ['name' => 'Sky', 'tagline' => 'Müasir qonaq dəsti', 'image' => 'https://embawood.az/image/catalog/new/SKy%20qonaq%20desti/sky_qonaq%20dəsti_komod.png'],
            ['name' => 'Angel', 'tagline' => 'Uşaq və gənc otağı', 'image' => 'https://embawood.az/image/catalog/Usaq%20və%20genc/Angel/angel_yataq_carpayi.png'],
        ];
    }

    /**
     * The 30 products in display order.
     *
     * @return list<array{name: string, price: int, image: string, category: string, material: string, color: string, dimensions: string, description: string, collection: string}>
     */
    public static function products(): array
    {
        return [
            ['name' => 'Lena 3 yerli divan', 'price' => 817, 'image' => 'https://embawood.az/image/catalog/Sofa%20set/Lena/lena%203%20yerli_2.png', 'category' => 'Yumşaq mebel', 'material' => 'Parça, elastik sükan', 'color' => 'Bej', 'dimensions' => '220×95×88 sm', 'description' => 'Geniş oturacaqlı, rahat 3 yerli divan.', 'collection' => 'Lena'],
            ['name' => 'Borneo 3 yerli divan', 'price' => 1782, 'image' => 'https://embawood.az/image/catalog/Yeni%20divanlar/borneo/borneo2.png', 'category' => 'Yumşaq mebel', 'material' => 'Premium parça', 'color' => 'Tünd boz', 'dimensions' => '245×98×90 sm', 'description' => 'Müasir dizayn, yüksək rahatlıq.', 'collection' => 'Borneo'],
            ['name' => 'Acelya Grey 3 yerli divan', 'price' => 1014, 'image' => 'https://embawood.az/image/catalog/Sofa%20set/Acelya%20grey/acelya_3_yerli_divan_1.png', 'category' => 'Yumşaq mebel', 'material' => 'Parça', 'color' => 'Boz', 'dimensions' => '230×92×86 sm', 'description' => 'Minimalist interyer üçün uyğun.', 'collection' => 'Acelya'],
            ['name' => 'Canary 3 yerli divan', 'price' => 857, 'image' => 'https://embawood.az/image/catalog/canary%203%20yerli_3.png', 'category' => 'Yumşaq mebel', 'material' => 'Parça', 'color' => 'Krem', 'dimensions' => '218×90×84 sm', 'description' => 'İşıqlı rəng palitrası.', 'collection' => 'Canary'],
            ['name' => 'Como 3 yerli divan', 'price' => 1008, 'image' => 'https://embawood.az/image/catalog/Yeni%20divanlar/como/como5.png', 'category' => 'Yumşaq mebel', 'material' => 'Parça, taxta ayaq', 'color' => 'Antrasit', 'dimensions' => '228×94×88 sm', 'description' => 'Yeni kolleksiya.', 'collection' => 'Como'],
            ['name' => 'Hilton 3 yerli divan', 'price' => 1792, 'image' => 'https://embawood.az/image/catalog/Yumusaq%20divanlar/Hilton/hilton_3%20yerli_1310x911_4.png', 'category' => 'Yumşaq mebel', 'material' => 'Premium parça', 'color' => 'Bej-qəhvəyi', 'dimensions' => '250×96×90 sm', 'description' => 'Premium salon divanı.', 'collection' => 'Hilton'],
            ['name' => 'Luca 3 yerli divan', 'price' => 808, 'image' => 'https://embawood.az/image/catalog/Yumusaq%20divanlar/luca%203%20yerli%20divan.png', 'category' => 'Yumşaq mebel', 'material' => 'Parça', 'color' => 'Açıq boz', 'dimensions' => '215×90×85 sm', 'description' => 'Kompakt mənzillər üçün.', 'collection' => 'Luca'],
            ['name' => 'Carino X 3 yerli divan', 'price' => 1248, 'image' => 'https://embawood.az/image/catalog/Sofa%20set/Carino/carino_divan.png', 'category' => 'Yumşaq mebel', 'material' => 'Parça, metal', 'color' => 'Gümüşü-boz', 'dimensions' => '235×93×87 sm', 'description' => 'Elegant müasir xətlər.', 'collection' => 'Carino'],
            ['name' => 'Liberia künc divan', 'price' => 1450, 'image' => 'https://embawood.az/image/catalog/Yumusaq%20divanlar/liberia_künc%201310x911_2%20(3).png', 'category' => 'Künc divanlar', 'material' => 'Parça', 'color' => 'Bej', 'dimensions' => '280×180×88 sm', 'description' => 'Modul künc divan.', 'collection' => 'Liberia'],
            ['name' => 'Yıldız yataq komod-güzgü', 'price' => 792, 'image' => 'https://embawood.az/image/catalog/Bedroom/Yildiz/trumo.png', 'category' => 'Komod və güzgülər', 'material' => 'Laminat, MDF', 'color' => 'Ağ-qəhvəyi', 'dimensions' => '140×45×85 sm', 'description' => 'Güzgülü komod.', 'collection' => 'Yıldız'],
            ['name' => 'Tiffany yataq komod-güzgü', 'price' => 364, 'image' => 'https://embawood.az/image/catalog/Bedroom/Tiffany/tiffany_yataq_trumo.png', 'category' => 'Komod və güzgülər', 'material' => 'MDF', 'color' => 'Ağ', 'dimensions' => '120×42×78 sm', 'description' => 'Kompakt komod.', 'collection' => 'Tiffany'],
            ['name' => 'Portofino tumba', 'price' => 112, 'image' => 'https://embawood.az/image/catalog/Bedroom/Portofino/tumba_1.png', 'category' => 'Komod və güzgülər', 'material' => 'Laminat', 'color' => 'Ağ', 'dimensions' => '45×40×48 sm', 'description' => 'Gecə tumba.', 'collection' => 'Portofino'],
            ['name' => 'Tiffany çarpayı', 'price' => 302, 'image' => 'https://embawood.az/image/catalog/pianta/pianta_yataq%20dəsti_carpayi.png', 'category' => 'Yataq mebeli', 'material' => 'Laminat karkas', 'color' => 'Ağ', 'dimensions' => '160×200 sm', 'description' => 'Tək nəfərlik çarpayı.', 'collection' => 'Tiffany'],
            ['name' => 'Portofino çarpayı', 'price' => 407, 'image' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/Siena%20yataq/siena%20yataq%20desti_carpayi.png', 'category' => 'Yataq mebeli', 'material' => 'MDF, laminat', 'color' => 'Ağ-qəhvəyi', 'dimensions' => '160×200 sm', 'description' => 'Portofino kolleksiyası.', 'collection' => 'Portofino'],
            ['name' => 'Kataniya çarpayı', 'price' => 399, 'image' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/melina/Melina%20çarpayı.png', 'category' => 'Yataq mebeli', 'material' => 'Laminat', 'color' => 'Ceviz', 'dimensions' => '160×200 sm', 'description' => 'Klassik xətlər.', 'collection' => 'Kataniya'],
            ['name' => 'Toskana çarpayı', 'price' => 401, 'image' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/efor/efor%20çarpayı_2.png', 'category' => 'Yataq mebeli', 'material' => 'MDF', 'color' => 'Ağ', 'dimensions' => '180×200 sm', 'description' => 'İki nəfərlik çarpayı.', 'collection' => 'Toskana'],
            ['name' => 'California çarpayı', 'price' => 421, 'image' => 'https://embawood.az/image/catalog/Amalfi/arpayı.jpg', 'category' => 'Yataq mebeli', 'material' => 'Laminat', 'color' => 'Boz', 'dimensions' => '160×200 sm', 'description' => 'Müasir yataq otağı.', 'collection' => 'California'],
            ['name' => 'Prada çarpayı', 'price' => 455, 'image' => 'https://embawood.az/image/catalog/Bedroom/Sanny/sany_yataq_carpayı.png', 'category' => 'Yataq mebeli', 'material' => 'MDF', 'color' => 'Tünd qəhvəyi', 'dimensions' => '180×200 sm', 'description' => 'Premium yataq kolleksiyası.', 'collection' => 'Prada'],
            ['name' => 'Angel gənc çarpayı', 'price' => 438, 'image' => 'https://embawood.az/image/catalog/Usaq%20və%20genc/Angel/angel_yataq_carpayi.png', 'category' => 'Uşaq və gənc', 'material' => 'Laminat', 'color' => 'Ağ-mavi', 'dimensions' => '90×190 sm', 'description' => 'Gənc otağı üçün.', 'collection' => 'Angel'],
            ['name' => 'Siena yataq dəsti çarpayı', 'price' => 1164, 'image' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/Siena%20yataq/siena%20yataq%20desti_carpayi.png', 'category' => 'Yataq mebeli', 'material' => 'MDF, laminat', 'color' => 'Ağ', 'dimensions' => '160×200 sm', 'description' => 'Tam yataq dəsti.', 'collection' => 'Siena'],
            ['name' => 'Sky qonaq dəsti komod', 'price' => 845, 'image' => 'https://embawood.az/image/catalog/new/SKy%20qonaq%20desti/sky_qonaq%20dəsti_komod.png', 'category' => 'Qonaq otağı', 'material' => 'Laminat', 'color' => 'Ağ-ceviz', 'dimensions' => '160×45×80 sm', 'description' => 'Qonaq dəsti komponenti.', 'collection' => 'Sky'],
            ['name' => 'Cross stul', 'price' => 59, 'image' => 'https://embawood.az/image/catalog/Stullar/Cross/cross_stul_1.png', 'category' => 'Masa və stullar', 'material' => 'Metal, parça', 'color' => 'Qara', 'dimensions' => '45×52×85 sm', 'description' => 'Ergonomik stul.', 'collection' => 'Cross'],
            ['name' => 'Classy döşək 160×200', 'price' => 149, 'image' => 'https://embawood.az/image/catalog/Matrass/Classy/classy_160x200.png', 'category' => 'Döşəklər', 'material' => 'Pocket yay', 'color' => 'Ağ', 'dimensions' => '160×200 sm', 'description' => 'Ortopedik dəstək.', 'collection' => 'Classy'],
            ['name' => 'Pianta yataq dəsti çarpayı', 'price' => 890, 'image' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/Pianta%20yataq%20desti/pianta_yataq%20dəsti_carpayi.png', 'category' => 'Yataq mebeli', 'material' => 'MDF', 'color' => 'Ağ', 'dimensions' => '160×200 sm', 'description' => 'Yeni kolleksiya.', 'collection' => 'Pianta'],
            ['name' => 'Melina çarpayı', 'price' => 520, 'image' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/melina/Melina%20çarpayı.png', 'category' => 'Yataq mebeli', 'material' => 'Laminat', 'color' => 'Açıq qəhvəyi', 'dimensions' => '160×200 sm', 'description' => 'İsti tonlar.', 'collection' => 'Melina'],
            ['name' => 'Efor çarpayı', 'price' => 380, 'image' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/efor/efor%20çarpayı_2.png', 'category' => 'Yataq mebeli', 'material' => 'MDF', 'color' => 'Ağ', 'dimensions' => '140×200 sm', 'description' => 'Kiçik otaqlar üçün.', 'collection' => 'Efor'],
            ['name' => 'Cross çarpayı MW 1600', 'price' => 454, 'image' => 'https://embawood.az/image/catalog/Yeni%20yataq%20destler/efor/efor%20çarpayı_2.png', 'category' => 'Yataq mebeli', 'material' => 'Laminat', 'color' => 'Boz', 'dimensions' => '160×200 sm', 'description' => 'Yaddaşlı başlıqlı.', 'collection' => 'Cross'],
            ['name' => 'Borneo künc modul', 'price' => 2100, 'image' => 'https://embawood.az/image/catalog/Borneo%20site/borneo1.jpg', 'category' => 'Künc divanlar', 'material' => 'Parça', 'color' => 'Antrasit', 'dimensions' => '300×200×90 sm', 'description' => 'Premium künc divan.', 'collection' => 'Borneo'],
            ['name' => 'Lena divan modulu', 'price' => 650, 'image' => 'https://embawood.az/image/catalog/Sofa%20set/Lena/lena%203%20yerli.png', 'category' => 'Yumşaq mebel', 'material' => 'Parça', 'color' => 'Bej', 'dimensions' => '120×95×88 sm', 'description' => 'Modul əlavə.', 'collection' => 'Lena'],
            ['name' => 'Hilton kreslo', 'price' => 420, 'image' => 'https://embawood.az/image/catalog/Yumusaq%20divanlar/Hilton/hilton_3%20yerli_1310x911_1.png', 'category' => 'Yumşaq mebel', 'material' => 'Parça', 'color' => 'Bej-qəhvəyi', 'dimensions' => '85×90×80 sm', 'description' => 'Hilton kreslo.', 'collection' => 'Hilton'],
        ];
    }

    /**
     * Reproduces the frontend's toProduct() derivation for the 1-based position.
     *
     * @return array{onSale: bool, isNew: bool, inStock: bool, oldPrice: int|null}
     */
    public static function derivedFlags(int $position, int $price): array
    {
        $onSale = 0 === $position % 4;

        return [
            'onSale' => $onSale,
            'isNew' => 0 === $position % 5,
            'inStock' => 0 !== $position % 11,
            'oldPrice' => $onSale ? (int) round($price * 1.18) : null,
        ];
    }
}
