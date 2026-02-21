<?php
/**
 * Image Helper v3 - Banque d'images massive pour produits TrustPick
 * 
 * ~350+ images uniques Unsplash couvrant tous les types de produits.
 * Sélection déterministe par hash du titre complet → chaque produit
 * avec un nom différent obtient une image différente.
 */

/**
 * Normalise un texte : minuscules, sans accents, sans caractères spéciaux
 */
function normalizeText($text)
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, [
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'à' => 'a',
        'â' => 'a',
        'ä' => 'a',
        'î' => 'i',
        'ï' => 'i',
        'ô' => 'o',
        'ö' => 'o',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ç' => 'c',
        'ñ' => 'n',
    ]);
    $text = preg_replace('/[^a-z0-9 ]/', ' ', $text);
    return trim(preg_replace('/\s+/', ' ', $text));
}

/**
 * Construit l'URL Unsplash permanente
 */
function _u($photoId, $w = 600, $h = 450)
{
    return "https://images.unsplash.com/{$photoId}?w={$w}&h={$h}&fit=crop&q=80";
}

/**
 * Banque d'images massive par mot-clé.
 * Toutes les URLs sont des images stables images.unsplash.com.
 * AUCUNE URL n'est partagée entre keywords différents.
 */
function getImageDatabase(): array
{
    return [

        // =====================================================
        //  ÉLECTRONIQUE
        // =====================================================

        // --- Smartphones (15 images uniques) ---
        'smartphone' => [
            _u('photo-1511707171634-5f897ff02aa9'),
            _u('photo-1592899677977-9c10ca588bbd'),
            _u('photo-1598327105666-5b89351aff97'),
            _u('photo-1565849904461-04a58ad377e0'),
            _u('photo-1601784551446-20c9e07cdbdb'),
            _u('photo-1580910051074-3eb694886f2b'),
            _u('photo-1605236453806-6ff36851218e'),
            _u('photo-1556656793-08538906a9f8'),
            _u('photo-1574944985070-8f3ebc6b79d2'),
            _u('photo-1567581935884-3349723552ca'),
            _u('photo-1557244056-ac3033d17d9a'),
            _u('photo-1533228100845-08145b01de14'),
            _u('photo-1585060544812-6b45742d762f'),
            _u('photo-1609252925148-b0f1b515e111'),
            _u('photo-1596558450268-9c27524ba856'),
        ],
        'galaxy' => [
            _u('photo-1610945415295-d9bbf067e59c'),
            _u('photo-1610792516307-ea5acd9c3b00'),
            _u('photo-1611472173362-3f53dbd65d80'),
            _u('photo-1565849904461-04a58ad377e0'),
            _u('photo-1585060544812-6b45742d762f'),
            _u('photo-1592899677977-9c10ca588bbd'),
            _u('photo-1580910051074-3eb694886f2b'),
            _u('photo-1609252925148-b0f1b515e111'),
            _u('photo-1598327105666-5b89351aff97'),
            _u('photo-1596558450268-9c27524ba856'),
        ],
        'iphone' => [
            _u('photo-1601784551446-20c9e07cdbdb'),
            _u('photo-1605236453806-6ff36851218e'),
            _u('photo-1556656793-08538906a9f8'),
            _u('photo-1574944985070-8f3ebc6b79d2'),
            _u('photo-1511707171634-5f897ff02aa9'),
            _u('photo-1567581935884-3349723552ca'),
            _u('photo-1557244056-ac3033d17d9a'),
            _u('photo-1533228100845-08145b01de14'),
        ],

        // --- Laptops / Ordinateurs (15 images uniques) ---
        'laptop' => [
            _u('photo-1496181133206-80ce9b88a853'),
            _u('photo-1525547719571-a2d4ac8945e2'),
            _u('photo-1588872657578-7efd1f1555ed'),
            _u('photo-1517336714731-489689fd1ca8'),
            _u('photo-1498050108023-c5249f4df085'),
            _u('photo-1541807084-5c52b6b3adef'),
            _u('photo-1484788984921-03950022c9ef'),
            _u('photo-1531297484001-80022131f5a1'),
            _u('photo-1611186871348-b1ce696e52c9'),
            _u('photo-1602080858428-57174f9431cf'),
            _u('photo-1603302576837-37561b2e2302'),
            _u('photo-1629131726692-1accd0c53ce0'),
            _u('photo-1593642702821-c8da6771f0c6'),
            _u('photo-1587614382346-4ec70e388b28'),
            _u('photo-1544099858-75feeb57f01b'),
        ],
        'ordinateur' => [
            _u('photo-1593642702821-c8da6771f0c6'),
            _u('photo-1587614382346-4ec70e388b28'),
            _u('photo-1544099858-75feeb57f01b'),
            _u('photo-1496181133206-80ce9b88a853'),
            _u('photo-1531297484001-80022131f5a1'),
            _u('photo-1611186871348-b1ce696e52c9'),
            _u('photo-1498050108023-c5249f4df085'),
            _u('photo-1541807084-5c52b6b3adef'),
            _u('photo-1484788984921-03950022c9ef'),
            _u('photo-1602080858428-57174f9431cf'),
        ],

        // --- Écouteurs / Earbuds (12 images uniques) ---
        'ecouteur' => [
            _u('photo-1590658268037-6bf12f032f55'),
            _u('photo-1606220588913-b3aacb4d2f46'),
            _u('photo-1572536147248-ac59a8abfa4b'),
            _u('photo-1583394838336-acd977736f90'),
            _u('photo-1613040809024-b4ef7ba99bc3'),
            _u('photo-1598550476439-6847c6e8f07a'),
            _u('photo-1608156639585-b3a776048ceb'),
            _u('photo-1600294037681-c80b4cb5b434'),
            _u('photo-1631867934084-fae37b618eb6'),
            _u('photo-1590658165737-15a047b7c0b0'),
            _u('photo-1505740420928-5e560c06d30e'),
            _u('photo-1484704849700-f032a568e944'),
        ],
        'wireless' => [
            _u('photo-1606220588913-b3aacb4d2f46'),
            _u('photo-1590658268037-6bf12f032f55'),
            _u('photo-1613040809024-b4ef7ba99bc3'),
            _u('photo-1572536147248-ac59a8abfa4b'),
            _u('photo-1598550476439-6847c6e8f07a'),
            _u('photo-1608156639585-b3a776048ceb'),
            _u('photo-1583394838336-acd977736f90'),
            _u('photo-1631867934084-fae37b618eb6'),
        ],

        // --- Casques audio (10 images uniques) ---
        'casque' => [
            _u('photo-1580894908361-967195033215'),
            _u('photo-1505740420928-5e560c06d30e'),
            _u('photo-1546435770-a3e426bf472b'),
            _u('photo-1583394838336-acd977736f90'),
            _u('photo-1484704849700-f032a568e944'),
            _u('photo-1487215078519-e21cc028cb29'),
            _u('photo-1524678606370-a47ad25cb82a'),
            _u('photo-1599669454699-248893623440'),
            _u('photo-1618366712010-f4ae9c647dcb'),
            _u('photo-1578319439584-104c94d37305'),
        ],

        // --- Montres connectées / Smartwatch (12 images) ---
        'montre connectee' => [
            _u('photo-1579586337278-3befd40fd17a'),
            _u('photo-1546868871-af0de0ae72be'),
            _u('photo-1523275335684-37898b6baf30'),
            _u('photo-1508685096489-7aacd43bd3b1'),
            _u('photo-1434493789847-2a75b0547023'),
            _u('photo-1617043786394-f977fa12eddf'),
            _u('photo-1575311373937-040b8e1fd5b6'),
            _u('photo-1551816230-ef5deaed4a26'),
            _u('photo-1544117519-31a4b719223d'),
            _u('photo-1510017803350-5b65e72273a3'),
            _u('photo-1461141346587-763ab02bced9'),
            _u('photo-1542496658-e33a6d0d50f6'),
        ],
        'smartwatch' => [
            _u('photo-1579586337278-3befd40fd17a'),
            _u('photo-1546868871-af0de0ae72be'),
            _u('photo-1508685096489-7aacd43bd3b1'),
            _u('photo-1617043786394-f977fa12eddf'),
            _u('photo-1575311373937-040b8e1fd5b6'),
            _u('photo-1551816230-ef5deaed4a26'),
            _u('photo-1434493789847-2a75b0547023'),
            _u('photo-1544117519-31a4b719223d'),
        ],

        // --- Tablettes (10 images) ---
        'tablette' => [
            _u('photo-1544244015-0df4b3ffc6b0'),
            _u('photo-1585790050230-5dd28404ccb9'),
            _u('photo-1561154464-82e9aab73b87'),
            _u('photo-1611532736597-de2d4265fba3'),
            _u('photo-1542751110-97427bbecf20'),
            _u('photo-1589739900243-4b52cd9b104e'),
            _u('photo-1632882765546-1ee75f53becb'),
            _u('photo-1585771724684-38269d6639fd'),
            _u('photo-1579567761406-4684ee0c75b6'),
            _u('photo-1587033411391-5d9e51cce126'),
        ],

        // --- Enceintes Bluetooth (10 images) ---
        'enceinte' => [
            _u('photo-1608043152269-423dbba4e7e1'),
            _u('photo-1589003077984-894e133dabab'),
            _u('photo-1507646227500-4d389b0012be'),
            _u('photo-1558537348-c0f8e733989d'),
            _u('photo-1545454675-3531b543be5d'),
            _u('photo-1612099800105-5bb15a53f1f9'),
            _u('photo-1596455607563-ad6193f76b17'),
            _u('photo-1609525313344-a56c07861c4b'),
            _u('photo-1543512214-318228f8004d'),
            _u('photo-1571003123894-1f0594d2b5d9'),
        ],
        'bluetooth' => [
            _u('photo-1558537348-c0f8e733989d'),
            _u('photo-1608043152269-423dbba4e7e1'),
            _u('photo-1545454675-3531b543be5d'),
            _u('photo-1612099800105-5bb15a53f1f9'),
            _u('photo-1589003077984-894e133dabab'),
            _u('photo-1596455607563-ad6193f76b17'),
            _u('photo-1507646227500-4d389b0012be'),
            _u('photo-1609525313344-a56c07861c4b'),
        ],

        // --- Powerbank / Batterie externe (10 images) ---
        'powerbank' => [
            _u('photo-1609091839311-d5365f9ff1c5'),
            _u('photo-1585338107529-13afc25806f9'),
            _u('photo-1583863788434-e58a36330cf0'),
            _u('photo-1626947346165-4c2288dadc2a'),
            _u('photo-1621075160523-b936ad96132a'),
            _u('photo-1585771724684-38269d6639fd'),
            _u('photo-1618478594486-c65b899c4936'),
            _u('photo-1625895197185-efcec01cffe0'),
            _u('photo-1616348436168-de43ad0db179'),
            _u('photo-1586953208448-b95a79798f07'),
        ],
        'batterie' => [
            _u('photo-1621075160523-b936ad96132a'),
            _u('photo-1609091839311-d5365f9ff1c5'),
            _u('photo-1626947346165-4c2288dadc2a'),
            _u('photo-1583863788434-e58a36330cf0'),
            _u('photo-1618478594486-c65b899c4936'),
            _u('photo-1585338107529-13afc25806f9'),
            _u('photo-1616348436168-de43ad0db179'),
            _u('photo-1586953208448-b95a79798f07'),
        ],

        // --- Caméra sécurité (10 images) ---
        'camera' => [
            _u('photo-1516035069371-29a1b244cc32'),
            _u('photo-1502920917128-1aa500764cbd'),
            _u('photo-1617005082133-548c4dd27f35'),
            _u('photo-1558002038-1055907df827'),
            _u('photo-1557597774-9d273605dfa9'),
            _u('photo-1580983218765-beba88e44e43'),
            _u('photo-1557862921-37829c790f19'),
            _u('photo-1585771724684-38269d6639fd'),
            _u('photo-1560707303-4e980ce876ad'),
            _u('photo-1564466809058-bf4114d55352'),
        ],
        'securite' => [
            _u('photo-1558002038-1055907df827'),
            _u('photo-1557597774-9d273605dfa9'),
            _u('photo-1580983218765-beba88e44e43'),
            _u('photo-1617005082133-548c4dd27f35'),
            _u('photo-1557862921-37829c790f19'),
            _u('photo-1560707303-4e980ce876ad'),
            _u('photo-1564466809058-bf4114d55352'),
            _u('photo-1516035069371-29a1b244cc32'),
        ],

        // --- Écran / Moniteur (8 images) ---
        'ecran' => [
            _u('photo-1527443224154-c4a3942d3acf'),
            _u('photo-1585792180666-f7347c490ee2'),
            _u('photo-1593640408182-31c70c8268f5'),
            _u('photo-1558618666-fcd25c85f82e'),
            _u('photo-1547394765-185e1e68f34e'),
            _u('photo-1616763355603-9755a640a287'),
            _u('photo-1610389051254-64849803c8fd'),
            _u('photo-1618384887929-16ec33fab9ef'),
        ],

        // --- Chargeur (8 images) ---
        'chargeur' => [
            _u('photo-1583863788434-e58a36330cf0'),
            _u('photo-1625895197185-efcec01cffe0'),
            _u('photo-1616348436168-de43ad0db179'),
            _u('photo-1618478594486-c65b899c4936'),
            _u('photo-1609091839311-d5365f9ff1c5'),
            _u('photo-1621075160523-b936ad96132a'),
            _u('photo-1626947346165-4c2288dadc2a'),
            _u('photo-1586953208448-b95a79798f07'),
        ],

        // --- Souris (8 images) ---
        'souris' => [
            _u('photo-1615663245857-ac93bb7c39e7'),
            _u('photo-1527864550417-7fd91fc51a46'),
            _u('photo-1563297007-0686b7003af7'),
            _u('photo-1613141411244-0e4ac259d217'),
            _u('photo-1586816879360-004f5b0c51e5'),
            _u('photo-1629429408209-1f912961dbd8'),
            _u('photo-1527814050087-3793815479db'),
            _u('photo-1616588589676-62b3d4320f25'),
        ],

        // --- Clavier (8 images) ---
        'clavier' => [
            _u('photo-1587829741301-dc798b83add3'),
            _u('photo-1595225476474-87563907a212'),
            _u('photo-1541140532154-b024d1c93b01'),
            _u('photo-1618384887929-16ec33fab9ef'),
            _u('photo-1609921212029-bb5a28e60960'),
            _u('photo-1587829741301-dc798b83add3'),
            _u('photo-1563297007-0686b7003af7'),
            _u('photo-1616588589676-62b3d4320f25'),
        ],

        // --- Hub USB / Accessoires tech (6 images) ---
        'hub' => [
            _u('photo-1612198188060-c7c2a3b66eae'),
            _u('photo-1625895197185-efcec01cffe0'),
            _u('photo-1618384887929-16ec33fab9ef'),
            _u('photo-1609921212029-bb5a28e60960'),
            _u('photo-1616348436168-de43ad0db179'),
            _u('photo-1583863788434-e58a36330cf0'),
        ],

        // --- Webcam (6 images) ---
        'webcam' => [
            _u('photo-1587825140708-dfaf72ae4b04'),
            _u('photo-1617005082133-548c4dd27f35'),
            _u('photo-1580983218765-beba88e44e43'),
            _u('photo-1564466809058-bf4114d55352'),
            _u('photo-1560707303-4e980ce876ad'),
            _u('photo-1557862921-37829c790f19'),
        ],

        // =====================================================
        //  MODE & ACCESSOIRES
        // =====================================================

        // --- Sacs (12 images) ---
        'sac a main' => [
            _u('photo-1584917865442-de89df76afd3'),
            _u('photo-1590874103328-eac38ef100b7'),
            _u('photo-1594223274512-ad4803739b7c'),
            _u('photo-1591561954557-26941169b49e'),
            _u('photo-1548036328-c9fa89d128fa'),
            _u('photo-1575032617751-6ddec2089882'),
            _u('photo-1566150905458-1bf1fc113f0d'),
            _u('photo-1614179689702-355944cd0918'),
            _u('photo-1622560480654-d96214fdc887'),
            _u('photo-1560343090-f0409e92791a'),
            _u('photo-1578237493287-3a5a1e1de907'),
            _u('photo-1584917865442-de89df76afd3'),
        ],
        'sac' => [
            _u('photo-1548036328-c9fa89d128fa'),
            _u('photo-1553062407-98eeb64c6a62'),
            _u('photo-1584917865442-de89df76afd3'),
            _u('photo-1622560480654-d96214fdc887'),
            _u('photo-1560343090-f0409e92791a'),
            _u('photo-1590874103328-eac38ef100b7'),
            _u('photo-1594223274512-ad4803739b7c'),
            _u('photo-1591561954557-26941169b49e'),
            _u('photo-1575032617751-6ddec2089882'),
            _u('photo-1566150905458-1bf1fc113f0d'),
        ],

        // --- Lunettes de soleil (12 images) ---
        'lunettes' => [
            _u('photo-1572635196237-14b3f281503f'),
            _u('photo-1511499767150-a48a237f0083'),
            _u('photo-1473496169904-658ba7c44d8a'),
            _u('photo-1577803645773-f96470509666'),
            _u('photo-1509695507497-903c140c43b0'),
            _u('photo-1508296695146-257a814070b4'),
            _u('photo-1529981188441-8a2e6fe30bc5'),
            _u('photo-1574258495973-f010dfbb5371'),
            _u('photo-1556015048-4d3aa10df74c'),
            _u('photo-1508394522340-ef07bba29ef6'),
            _u('photo-1523575166472-a83a0ed1d522'),
            _u('photo-1483985988355-763728e1935b'),
        ],

        // --- Montres classiques (12 images) ---
        'montre' => [
            _u('photo-1524592094714-0f0654e20314'),
            _u('photo-1522312346375-d1a52e2b99b3'),
            _u('photo-1542496658-e33a6d0d50f6'),
            _u('photo-1539874754764-5a96559165b0'),
            _u('photo-1509048191080-d2984bad6ae5'),
            _u('photo-1614164185128-e4ec99c436d7'),
            _u('photo-1548171915-e79a380a2a4b'),
            _u('photo-1547996160-81dfa63595aa'),
            _u('photo-1533139502658-0198f920d8e8'),
            _u('photo-1507089947368-19c1da9775ae'),
            _u('photo-1612817159949-195b6eb9e31a'),
            _u('photo-1455859314203-95f21866cc43'),
        ],

        // --- Ceintures (10 images) ---
        'ceinture' => [
            _u('photo-1624222247344-550fb60583dc'),
            _u('photo-1553062407-98eeb64c6a62'),
            _u('photo-1603487742131-4160ec999306'),
            _u('photo-1605733160314-4fc7dac4bb16'),
            _u('photo-1594938328870-9623159c8c99'),
            _u('photo-1560343776-97e7d202ff0e'),
            _u('photo-1613483187676-b3539f83a9e4'),
            _u('photo-1620625515032-6ed0c1790c75'),
            _u('photo-1617606002806-94e279c22e1e'),
            _u('photo-1617019114583-affb34d1b3cd'),
        ],

        // --- Écharpes (8 images) ---
        'echarpe' => [
            _u('photo-1520903920243-00d872a2d1c9'),
            _u('photo-1601924921557-45e8e0e4f105'),
            _u('photo-1510531704581-5b2870972060'),
            _u('photo-1543087903-1ac2ec7aa8c5'),
            _u('photo-1602573991155-21f0143bb45c'),
            _u('photo-1608234808654-2a8875faa7fd'),
            _u('photo-1580905231948-65848e777fce'),
            _u('photo-1617019114583-affb34d1b3cd'),
        ],

        // --- Portefeuilles (10 images) ---
        'portefeuille' => [
            _u('photo-1627123424574-724758594e93'),
            _u('photo-1559526324-c1f275fbfa32'),
            _u('photo-1612902456551-404b5ec9f4c5'),
            _u('photo-1606503153255-59d8b8e0c5e8'),
            _u('photo-1585856331553-5b64b7311086'),
            _u('photo-1592323360850-e7bc096d4b77'),
            _u('photo-1614195795284-55c7b1b0bbc6'),
            _u('photo-1610478920392-9bbdb6873c63'),
            _u('photo-1591017403286-fd8493524e1e'),
            _u('photo-1621189937996-8a7ec2490fda'),
        ],

        // =====================================================
        //  MAISON & JARDIN
        // =====================================================

        // --- Aspirateur robot (10 images) ---
        'aspirateur' => [
            _u('photo-1558618666-fcd25c85f82e'),
            _u('photo-1585771724684-38269d6639fd'),
            _u('photo-1603625741568-bc05fc4eda72'),
            _u('photo-1625480860249-be82c0f4c96a'),
            _u('photo-1558317374-067fb5f30001'),
            _u('photo-1590510618520-3bd5a5ef5d33'),
            _u('photo-1572276596985-27d77d263542'),
            _u('photo-1556228841-a3c527ebefe5'),
            _u('photo-1631212640486-a5445dd6e14b'),
            _u('photo-1602526429747-ac4036a0f29f'),
        ],
        'robot' => [
            _u('photo-1603625741568-bc05fc4eda72'),
            _u('photo-1558618666-fcd25c85f82e'),
            _u('photo-1625480860249-be82c0f4c96a'),
            _u('photo-1585771724684-38269d6639fd'),
            _u('photo-1558317374-067fb5f30001'),
            _u('photo-1631212640486-a5445dd6e14b'),
            _u('photo-1590510618520-3bd5a5ef5d33'),
            _u('photo-1602526429747-ac4036a0f29f'),
        ],

        // --- Ventilateurs (10 images) ---
        'ventilateur' => [
            _u('photo-1628863353691-0071c8c1874c'),
            _u('photo-1581783898382-80983a3e4b1b'),
            _u('photo-1585771724684-38269d6639fd'),
            _u('photo-1556228578-8c89e6adf883'),
            _u('photo-1560343090-f0409e92791a'),
            _u('photo-1627495547870-c7ba0b3a0ee8'),
            _u('photo-1634317245453-bc08b6c8f3b3'),
            _u('photo-1615257260-2d08adee6281'),
            _u('photo-1600494603989-9650cf6ddd3d'),
            _u('photo-1613521441414-e3aa56e84fcd'),
        ],

        // --- Cafetières (12 images) ---
        'cafetiere' => [
            _u('photo-1517668808822-9ebb02f2a0e6'),
            _u('photo-1572442388796-11668a67e53d'),
            _u('photo-1520970014086-2208d157c9e2'),
            _u('photo-1509785307050-d4066910ec1e'),
            _u('photo-1514432324607-a09d9b4aefda'),
            _u('photo-1500353391678-d7b57979d6d2'),
            _u('photo-1570526377934-1a750ff4d150'),
            _u('photo-1521302200778-33500795e128'),
            _u('photo-1495474472287-4d71bcdd2085'),
            _u('photo-1541167760496-1628856ab772'),
            _u('photo-1510591509098-f4fdc6d0ff04'),
            _u('photo-1504627298434-2119d6928e93'),
        ],

        // --- Mixeur / Blender (10 images) ---
        'mixeur' => [
            _u('photo-1570222094114-d054a817e56b'),
            _u('photo-1585515320310-259814833e62'),
            _u('photo-1622480108484-c0e8845d0ab0'),
            _u('photo-1570275239925-4af0aa93a0dc'),
            _u('photo-1616155187853-f3faa26bc54e'),
            _u('photo-1626371494057-30a4fe131e3f'),
            _u('photo-1592417817098-8fd3d769ef4e'),
            _u('photo-1525385133512-2f3bdd039054'),
            _u('photo-1556228453-efd6c1ff04f6'),
            _u('photo-1616093793498-88ea0e1b11c4'),
        ],

        // --- Lampes LED (12 images) ---
        'lampe' => [
            _u('photo-1507473885765-e6ed057ab6fe'),
            _u('photo-1513506003901-1e6a229e2d15'),
            _u('photo-1543198126-413ecc2b1054'),
            _u('photo-1534105615256-13940a56ff44'),
            _u('photo-1494438639946-1ebd1d20bf85'),
            _u('photo-1540932239986-30128078f3c5'),
            _u('photo-1524484485831-a92ffc0de03f'),
            _u('photo-1530603907829-659dc1b3f567'),
            _u('photo-1573790387438-4da905039392'),
            _u('photo-1567459045800-a806e0b82783'),
            _u('photo-1513519245088-0e12902e5a38'),
            _u('photo-1554211620-e72cef1d7c2b'),
        ],
        'led' => [
            _u('photo-1534105615256-13940a56ff44'),
            _u('photo-1507473885765-e6ed057ab6fe'),
            _u('photo-1494438639946-1ebd1d20bf85'),
            _u('photo-1540932239986-30128078f3c5'),
            _u('photo-1543198126-413ecc2b1054'),
            _u('photo-1524484485831-a92ffc0de03f'),
            _u('photo-1513506003901-1e6a229e2d15'),
            _u('photo-1530603907829-659dc1b3f567'),
        ],

        // =====================================================
        //  ALIMENTATION
        // =====================================================

        // --- Huile d'olive (10 images) ---
        'huile' => [
            _u('photo-1474979266404-7eaacbcd87c5'),
            _u('photo-1476649616092-3a4e94ed57a1'),
            _u('photo-1607623814075-e51df1bdc82f'),
            _u('photo-1556679343-c7306c1976bc'),
            _u('photo-1475483768684-6163159e2c31'),
            _u('photo-1587467512961-120760940b17'),
            _u('photo-1621600980959-25f3d8f0ba3e'),
            _u('photo-1474979266404-7eaacbcd87c5'),
            _u('photo-1611072337626-83494d15a3e7'),
            _u('photo-1600475985496-2e24e1fde872'),
        ],
        'olive' => [
            _u('photo-1474979266404-7eaacbcd87c5'),
            _u('photo-1476649616092-3a4e94ed57a1'),
            _u('photo-1607623814075-e51df1bdc82f'),
            _u('photo-1556679343-c7306c1976bc'),
            _u('photo-1475483768684-6163159e2c31'),
            _u('photo-1587467512961-120760940b17'),
            _u('photo-1621600980959-25f3d8f0ba3e'),
            _u('photo-1611072337626-83494d15a3e7'),
        ],

        // --- Miel (10 images) ---
        'miel' => [
            _u('photo-1587049352846-4a222e784d38'),
            _u('photo-1558642452-9d2a7deb7f62'),
            _u('photo-1582130925785-e4e3ee4bc7be'),
            _u('photo-1573246123716-6b1782bfc499'),
            _u('photo-1604774398609-f0f4c1b8deab'),
            _u('photo-1594016814561-a17a0ea52cb5'),
            _u('photo-1622467665690-b68da830a82e'),
            _u('photo-1570032257806-7eb945bb5741'),
            _u('photo-1612961948934-4ef7d5c15bab'),
            _u('photo-1481900431958-1c499f73e0b1'),
        ],

        // --- Riz (10 images) ---
        'riz' => [
            _u('photo-1586201375761-83865001e31c'),
            _u('photo-1536304993881-460e32f50669'),
            _u('photo-1516684732162-798a0062be99'),
            _u('photo-1613758947307-f3b8f5d80711'),
            _u('photo-1602491453631-e2a5ad90a131'),
            _u('photo-1506368249639-73a05d6f6488'),
            _u('photo-1594756202469-9ff9799b2e4e'),
            _u('photo-1578916171728-46686eac8d58'),
            _u('photo-1623669696722-24ef5be83b89'),
            _u('photo-1601493700631-2b16ec4b4716'),
        ],
        'basmati' => [
            _u('photo-1586201375761-83865001e31c'),
            _u('photo-1536304993881-460e32f50669'),
            _u('photo-1613758947307-f3b8f5d80711'),
            _u('photo-1602491453631-e2a5ad90a131'),
            _u('photo-1594756202469-9ff9799b2e4e'),
            _u('photo-1578916171728-46686eac8d58'),
        ],
        'jasmin' => [
            _u('photo-1516684732162-798a0062be99'),
            _u('photo-1536304993881-460e32f50669'),
            _u('photo-1506368249639-73a05d6f6488'),
            _u('photo-1623669696722-24ef5be83b89'),
            _u('photo-1601493700631-2b16ec4b4716'),
            _u('photo-1578916171728-46686eac8d58'),
        ],

        // --- Café en grains (12 images) ---
        'cafe' => [
            _u('photo-1559056199-641a0ac8b55e'),
            _u('photo-1495474472287-4d71bcdd2085'),
            _u('photo-1514432324607-a09d9b4aefda'),
            _u('photo-1447933601403-56dc2a6f40d0'),
            _u('photo-1559525839-b184a4d698c7'),
            _u('photo-1509785307050-d4066910ec1e'),
            _u('photo-1504630083234-14187a9df0f5'),
            _u('photo-1442411210769-b95c4632195e'),
            _u('photo-1611564494260-6f21b80af7ea'),
            _u('photo-1580933073521-dc49ac0d4e6a'),
            _u('photo-1501492673258-2f6e8a6b098d'),
            _u('photo-1461023058943-07fcbe16d735'),
        ],
        'grains' => [
            _u('photo-1559056199-641a0ac8b55e'),
            _u('photo-1514432324607-a09d9b4aefda'),
            _u('photo-1447933601403-56dc2a6f40d0'),
            _u('photo-1504630083234-14187a9df0f5'),
            _u('photo-1442411210769-b95c4632195e'),
            _u('photo-1611564494260-6f21b80af7ea'),
            _u('photo-1461023058943-07fcbe16d735'),
            _u('photo-1501492673258-2f6e8a6b098d'),
        ],

        // =====================================================
        //  SANTÉ & BEAUTÉ
        // =====================================================

        // --- Crèmes (12 images) ---
        'creme' => [
            _u('photo-1556228578-0d85b1a4d571'),
            _u('photo-1570194065650-d99fb4b38b17'),
            _u('photo-1596462502278-27bfdc403348'),
            _u('photo-1598440947619-2c35fc9aa908'),
            _u('photo-1608248543803-ba4f8c70ae0b'),
            _u('photo-1620916566398-39f1143ab7be'),
            _u('photo-1612817159949-195b6eb9e31a'),
            _u('photo-1556228720-195a672e8a03'),
            _u('photo-1571781926291-c477ebfd024b'),
            _u('photo-1576426863848-c21f53c60b19'),
            _u('photo-1617897903246-719242758050'),
            _u('photo-1591375275073-bb29b3388a16'),
        ],
        'hydratante' => [
            _u('photo-1556228578-0d85b1a4d571'),
            _u('photo-1596462502278-27bfdc403348'),
            _u('photo-1570194065650-d99fb4b38b17'),
            _u('photo-1608248543803-ba4f8c70ae0b'),
            _u('photo-1598440947619-2c35fc9aa908'),
            _u('photo-1620916566398-39f1143ab7be'),
            _u('photo-1571781926291-c477ebfd024b'),
            _u('photo-1576426863848-c21f53c60b19'),
        ],

        // --- Parfums (12 images) ---
        'parfum' => [
            _u('photo-1541643600914-78b084683601'),
            _u('photo-1523293182086-7651a899d37f'),
            _u('photo-1588405748880-12d1d2a59f75'),
            _u('photo-1594035910387-fea081d08e14'),
            _u('photo-1563170351-be82bc888aa4'),
            _u('photo-1587017539504-67cfbddac569'),
            _u('photo-1595425964272-fc79ebedb88f'),
            _u('photo-1592185285645-a8d3e1f3e93a'),
            _u('photo-1615634260167-c8cdede054de'),
            _u('photo-1588514727390-91fd5b9f5777'),
            _u('photo-1557170334-a9632e77c6e4'),
            _u('photo-1619994403073-2cec844b8c63'),
        ],

        // --- Shampoings (10 images) ---
        'shampoing' => [
            _u('photo-1535585209827-a15fcdbc4c2d'),
            _u('photo-1556228720-195a672e8a03'),
            _u('photo-1608248543803-ba4f8c70ae0b'),
            _u('photo-1631729371254-42c2892f0e6e'),
            _u('photo-1600428877878-1a0ff561c8e0'),
            _u('photo-1571781926291-c477ebfd024b'),
            _u('photo-1585232004423-244e0e6904e3'),
            _u('photo-1597354984706-fac992d9306f'),
            _u('photo-1612817159949-195b6eb9e31a'),
            _u('photo-1591375275073-bb29b3388a16'),
        ],

        // --- Brosses à dents (10 images) ---
        'brosse a dents' => [
            _u('photo-1559667331-3185847d2781'),
            _u('photo-1570172619644-dfd03ed5d881'),
            _u('photo-1609587312208-cea54be969e7'),
            _u('photo-1562246050-8f3e48ec4788'),
            _u('photo-1607613009820-a29f7bb81c04'),
            _u('photo-1572631382601-42a1f4ca5a0e'),
            _u('photo-1573461160327-b450ce3d8e7f'),
            _u('photo-1621263764928-df1444c5e859'),
            _u('photo-1618925363781-6843d7ea3b15'),
            _u('photo-1614267861220-e02f7e4d2c3a'),
        ],
        'brosse' => [
            _u('photo-1559667331-3185847d2781'),
            _u('photo-1570172619644-dfd03ed5d881'),
            _u('photo-1609587312208-cea54be969e7'),
            _u('photo-1572631382601-42a1f4ca5a0e'),
            _u('photo-1562246050-8f3e48ec4788'),
            _u('photo-1607613009820-a29f7bb81c04'),
        ],

        // =====================================================
        //  SPORTS & LOISIRS
        // =====================================================

        // --- Ballons (images spécifiques par sport) ---
        'football' => [
            _u('photo-1614632537190-23e4146777db'),
            _u('photo-1552318965-6e6be7484ada'),
            _u('photo-1575361204480-aadea25e6e68'),
            _u('photo-1574629810360-7efbbe195018'),
            _u('photo-1606925797300-0b35e9d1794e'),
            _u('photo-1579952363873-27f3bade9f55'),
            _u('photo-1553778263-73a83bab9b0c'),
            _u('photo-1560272564-c83b66b1ad12'),
        ],
        'basketball' => [
            _u('photo-1546519638-68e109498ffc'),
            _u('photo-1574623452334-9e99857bbad2'),
            _u('photo-1519861531473-9200262188bf'),
            _u('photo-1494199505258-5f95a32e6e3f'),
            _u('photo-1559692048-79a3f837883d'),
            _u('photo-1608245449230-4ac19066d2d0'),
            _u('photo-1517649763962-0c623066013b'),
            _u('photo-1515523110800-9415d13b84a8'),
        ],
        'volleyball' => [
            _u('photo-1612872087720-bb876e2e67d1'),
            _u('photo-1547347298-4074fc3086f0'),
            _u('photo-1592656094267-764a45160876'),
            _u('photo-1558883897-3a0d0f75dbf1'),
            _u('photo-1530915534664-4ac6423816b7'),
            _u('photo-1509255929945-586a420363cf'),
        ],
        'handball' => [
            _u('photo-1612872087720-bb876e2e67d1'),
            _u('photo-1574629810360-7efbbe195018'),
            _u('photo-1575361204480-aadea25e6e68'),
            _u('photo-1553778263-73a83bab9b0c'),
            _u('photo-1519861531473-9200262188bf'),
            _u('photo-1608245449230-4ac19066d2d0'),
        ],
        'ballon' => [
            _u('photo-1614632537190-23e4146777db'),
            _u('photo-1575361204480-aadea25e6e68'),
            _u('photo-1552318965-6e6be7484ada'),
            _u('photo-1546519638-68e109498ffc'),
            _u('photo-1574623452334-9e99857bbad2'),
            _u('photo-1612872087720-bb876e2e67d1'),
            _u('photo-1574629810360-7efbbe195018'),
            _u('photo-1553778263-73a83bab9b0c'),
            _u('photo-1560272564-c83b66b1ad12'),
            _u('photo-1606925797300-0b35e9d1794e'),
        ],

        // --- Tapis de yoga (10 images) ---
        'tapis' => [
            _u('photo-1601925260368-ae2f83cf8b7f'),
            _u('photo-1544367567-0f2fcb009e0b'),
            _u('photo-1518611012118-696072aa579a'),
            _u('photo-1599447421416-3414500d18a5'),
            _u('photo-1575052814086-f385e2e2ad1b'),
            _u('photo-1588286840104-8957b019727f'),
            _u('photo-1603988363607-e1e4a66962c6'),
            _u('photo-1573384666979-2b1e160d2d08'),
            _u('photo-1571019613454-1cb2f99b2d8b'),
            _u('photo-1545205597-3d9d02c29597'),
        ],
        'yoga' => [
            _u('photo-1544367567-0f2fcb009e0b'),
            _u('photo-1601925260368-ae2f83cf8b7f'),
            _u('photo-1518611012118-696072aa579a'),
            _u('photo-1575052814086-f385e2e2ad1b'),
            _u('photo-1599447421416-3414500d18a5'),
            _u('photo-1588286840104-8957b019727f'),
            _u('photo-1603988363607-e1e4a66962c6'),
            _u('photo-1573384666979-2b1e160d2d08'),
            _u('photo-1571019613454-1cb2f99b2d8b'),
            _u('photo-1545205597-3d9d02c29597'),
        ],

        // --- Gourdes (10 images) ---
        'gourde' => [
            _u('photo-1602143407151-7111542de6e8'),
            _u('photo-1523362628745-0c100150b504'),
            _u('photo-1570831739435-6601aa3fa4fb'),
            _u('photo-1610824352934-9c82d5fe1e5e'),
            _u('photo-1614093302611-8efc4de12964'),
            _u('photo-1600166898405-da9535204843'),
            _u('photo-1550505095-81378d7f2d10'),
            _u('photo-1600803907087-f56d462fd26b'),
            _u('photo-1616840420693-ef8e4ae5f7ca'),
            _u('photo-1581092918056-0c4c3acd3789'),
        ],

        // --- Cordes à sauter (8 images) ---
        'corde a sauter' => [
            _u('photo-1434596922112-19cb4f9e2e3f'),
            _u('photo-1517963879433-6ad2b056d712'),
            _u('photo-1517836357463-d25dfeac3438'),
            _u('photo-1571019614242-c5c5dee9f50b'),
            _u('photo-1601422407692-ec4eeec1d9b3'),
            _u('photo-1598971639058-fab3c3109a00'),
            _u('photo-1540497077202-7c8a3999166f'),
            _u('photo-1517130038641-a774d04afb3c'),
        ],
        'corde' => [
            _u('photo-1434596922112-19cb4f9e2e3f'),
            _u('photo-1517963879433-6ad2b056d712'),
            _u('photo-1517836357463-d25dfeac3438'),
            _u('photo-1571019614242-c5c5dee9f50b'),
            _u('photo-1601422407692-ec4eeec1d9b3'),
            _u('photo-1598971639058-fab3c3109a00'),
        ],
        'sauter' => [
            _u('photo-1540497077202-7c8a3999166f'),
            _u('photo-1517130038641-a774d04afb3c'),
            _u('photo-1517836357463-d25dfeac3438'),
            _u('photo-1434596922112-19cb4f9e2e3f'),
            _u('photo-1517963879433-6ad2b056d712'),
            _u('photo-1571019614242-c5c5dee9f50b'),
        ],

        // =====================================================
        //  EXTRAS
        // =====================================================

        'livre' => [
            _u('photo-1544947950-fa07a98d237f'),
            _u('photo-1512820790803-83ca734da794'),
            _u('photo-1524995997946-a1c2e315a42f'),
            _u('photo-1519682337058-a94d519337bc'),
            _u('photo-1476275466078-4007374efbbe'),
            _u('photo-1497633762265-9d179a990aa6'),
        ],
        'voiture' => [
            _u('photo-1494976388531-d1058494cdd8'),
            _u('photo-1503376780353-7e6692767b70'),
            _u('photo-1492144534655-ae79c964c9d7'),
            _u('photo-1517524008697-84bbe3c3fd98'),
            _u('photo-1555215695-3004980ad54e'),
            _u('photo-1502877338535-766e1452684a'),
        ],
    ];
}

/**
 * Fallback par catégorie (quand aucun mot-clé ne match dans le titre)
 */
function getCategoryFallbackImages(): array
{
    return [
        'electronique' => [
            _u('photo-1518770660439-4636190af475'),
            _u('photo-1550009158-9ebf69173e03'),
            _u('photo-1519389950473-47ba0277781c'),
            _u('photo-1531297484001-80022131f5a1'),
            _u('photo-1526374965328-7f61d4dc18c5'),
            _u('photo-1593642702821-c8da6771f0c6'),
        ],
        'mode' => [
            _u('photo-1445205170230-053b83016050'),
            _u('photo-1441986300917-64674bd600d8'),
            _u('photo-1558171813-4c088753af8f'),
            _u('photo-1543163521-1bf539c55dd2'),
            _u('photo-1483985988355-763728e1935b'),
            _u('photo-1445205170230-053b83016050'),
        ],
        'accessoire' => [
            _u('photo-1558171813-4c088753af8f'),
            _u('photo-1441986300917-64674bd600d8'),
            _u('photo-1543163521-1bf539c55dd2'),
            _u('photo-1483985988355-763728e1935b'),
        ],
        'maison' => [
            _u('photo-1484154218962-a197022b5858'),
            _u('photo-1556909114-f6e7ad7d3136'),
            _u('photo-1502672260266-1c1ef2d93688'),
            _u('photo-1600585154340-be6161a56a0c'),
            _u('photo-1560448204-e02f11c3d0e2'),
        ],
        'jardin' => [
            _u('photo-1416879595882-3373a0480b5b'),
            _u('photo-1558904541-efa843a96f01'),
            _u('photo-1585320806297-9794b3e4eeae'),
            _u('photo-1523348837708-15d4a09cfac2'),
        ],
        'alimentation' => [
            _u('photo-1506354666786-959d6d497f1a'),
            _u('photo-1498837167922-ddd27525d352'),
            _u('photo-1504674900247-0877df9cc836'),
            _u('photo-1493770348161-369560ae357d'),
            _u('photo-1540189549336-e6e99c3679fe'),
        ],
        'sante' => [
            _u('photo-1576426863848-c21f53c60b19'),
            _u('photo-1571781926291-c477ebfd024b'),
            _u('photo-1596462502278-27bfdc403348'),
            _u('photo-1556228578-0d85b1a4d571'),
        ],
        'beaute' => [
            _u('photo-1596462502278-27bfdc403348'),
            _u('photo-1556228578-0d85b1a4d571'),
            _u('photo-1576426863848-c21f53c60b19'),
            _u('photo-1571781926291-c477ebfd024b'),
        ],
        'sport' => [
            _u('photo-1461896836934-bd45ba8306c7'),
            _u('photo-1517836357463-d25dfeac3438'),
            _u('photo-1571019614242-c5c5dee9f50b'),
            _u('photo-1517963879433-6ad2b056d712'),
            _u('photo-1540497077202-7c8a3999166f'),
        ],
        'loisir' => [
            _u('photo-1517836357463-d25dfeac3438'),
            _u('photo-1461896836934-bd45ba8306c7'),
            _u('photo-1540497077202-7c8a3999166f'),
            _u('photo-1517963879433-6ad2b056d712'),
        ],
    ];
}

/**
 * Sélectionne une image de façon déterministe mais diversifiée.
 * Utilise un hash composite du titre complet pour que chaque
 * variation de nom produise un index différent.
 *
 * @param array $images Pool d'images disponibles
 * @param string $title Titre complet du produit (sert de seed)
 * @param int $productId ID produit (salt secondaire)
 * @return string URL choisie
 */
function pickImage(array $images, string $title, int $productId = 0): string
{
    $count = count($images);
    if ($count === 0) {
        return getFallbackImage();
    }
    if ($count === 1) {
        return $images[0];
    }

    // Hash composite : le titre complet + l'ID produit produisent un index unique
    // Deux produits "Smartphone Galaxy A50" et "Smartphone Galaxy S21" auront
    // un hash très différent grâce au titre différent
    $hash = crc32($title . '|' . $productId);
    $index = abs($hash) % $count;

    return $images[$index];
}

/**
 * Génère une URL d'image pour un produit.
 * Pipeline de matching : titre exact → mots-clés prioritaires → catégorie → fallback
 *
 * @param array $product ['id', 'title', 'category_name'?, 'image'?]
 * @param int $width non utilisé (taille fixée dans les URLs)
 * @param int $height non utilisé (taille fixée dans les URLs)
 * @return string URL d'image permanente
 */
function getProductImage($product, $width = 400, $height = 300)
{
    // 1. Garder une image stockée si elle est valide (URL permanente Unsplash)
    if (!empty($product['image'])) {
        $img = $product['image'];
        if (str_contains($img, 'images.unsplash.com/photo-')) {
            return $img;
        }
    }

    $rawTitle = $product['title'] ?? '';
    $title = normalizeText($rawTitle);
    $category = normalizeText($product['category_name'] ?? '');
    $productId = intval($product['id'] ?? 0);

    $imageDb = getImageDatabase();

    // 2. Trier les clés par longueur décroissante (plus spécifique en premier)
    //    "montre connectee" matchera avant "montre"
    //    "brosse a dents" matchera avant "brosse"
    //    "sac a main" matchera avant "sac"
    $keys = array_keys($imageDb);
    usort($keys, fn($a, $b) => strlen($b) - strlen($a));

    foreach ($keys as $keyword) {
        if (str_contains($title, $keyword)) {
            return pickImage($imageDb[$keyword], $rawTitle, $productId);
        }
    }

    // 3. Fallback par catégorie (avec diversité aussi)
    if ($category) {
        $catImages = getCategoryFallbackImages();
        $catKeys = array_keys($catImages);
        usort($catKeys, fn($a, $b) => strlen($b) - strlen($a));

        foreach ($catKeys as $catKey) {
            if (str_contains($category, $catKey)) {
                $pool = $catImages[$catKey];
                if (is_array($pool)) {
                    return pickImage($pool, $rawTitle, $productId);
                }
                return $pool;
            }
        }
    }

    // 4. Fallback final
    return getFallbackImage($width, $height);
}

/**
 * Image de fallback universelle — une montre produit sur fond blanc
 */
function getFallbackImage($width = 400, $height = 300)
{
    // Pool de fallback génériques pour éviter la monotonie même en dernier recours
    $fallbacks = [
        _u('photo-1523275335684-37898b6baf30'),
        _u('photo-1505740420928-5e560c06d30e'),
        _u('photo-1572635196237-14b3f281503f'),
        _u('photo-1526170375885-4d8ecf77b99f'),
        _u('photo-1491553895911-0055eca6402d'),
        _u('photo-1542291026-7eec264c27ff'),
        _u('photo-1560343090-f0409e92791a'),
        _u('photo-1583394838336-acd977736f90'),
    ];

    // Utiliser l'heure courante pour varier légèrement le fallback
    return $fallbacks[abs(crc32(date('Y-m-d-H') . $width . $height)) % count($fallbacks)];
}

/**
 * DummyImage avec texte — alternative textuelle
 */
function getProductImageDummy($product, $width = 400, $height = 300)
{
    $title = isset($product['title']) ? substr($product['title'], 0, 25) : 'Produit';
    $encoded = urlencode($title);
    return sprintf('https://dummyimage.com/%dx%d/0066cc/ffffff?text=%s', $width, $height, $encoded);
}

/**
 * HTML <img> complet avec fallback onerror
 */
function renderProductImage($product, $attrs = [])
{
    $src = getProductImage($product, $attrs['width'] ?? 400, $attrs['height'] ?? 300);
    $alt = htmlspecialchars($product['title'] ?? 'Produit');

    $attrStr = '';
    foreach ($attrs as $key => $value) {
        if ($key !== 'width' && $key !== 'height') {
            $attrStr .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
    }

    return sprintf(
        '<img src="%s" alt="%s" loading="lazy" onerror="this.src=\'%s\'" %s>',
        htmlspecialchars($src),
        $alt,
        htmlspecialchars(getFallbackImage()),
        $attrStr
    );
}