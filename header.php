<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="description" content="">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
          integrity="sha512-1cK78a1o+ht2JcaW6g8OXYwqpev9+6GqOkz9xmBN9iUUhIndKtxwILGWYOSibOKjLsEdjyjZvYDq/cZwNeak0w=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= favicon_path() ?>/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= favicon_path() ?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= favicon_path() ?>/favicon-16x16.png">
    <link rel="manifest" href="<?= favicon_path() ?>/site.webmanifest">
    <link rel="mask-icon" href="<?= favicon_path() ?>/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>3M Barbers</title>
    <?php wp_head(); ?>
</head>
<body>
<header id="header" :class="[isOpened ? 'openedNavigation' : '', hasStickyHeader ? 'sticky' : '']">
    <div class="bg"></div>
    <div class="container">
        <div class="header-wrapper">
            <?= svgIcon(image_path(false) . "/logo-text.svg", ['class' => ['logo']]) ?>
            <div class="toggler" :class="isOpened ? 'active' : ''" @click="isOpened = !isOpened">
                <span class="one"></span>
                <span class="two"></span>
                <span class="three"></span>
            </div>
            <ul class="navigation d-md-flex d-none">
                <li class="nav-part">
                    <a class="nav-link" href="#sluzby">Služby</a>
                </li>
                <li class="nav-part">
                    <a class="nav-link" href="#onas">O nás</a>
                </li>
                <li class="nav-part">
                    <a class="nav-link" href="#kontakt">Kontakt</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="navigation-mobile">
        <div class="container">
            <ul class="navigation d-md-none d-flex">
                <li class="nav-part">
                    <a class="nav-link" href="#sluzby">Služby</a>
                </li>
                <li class="nav-part">
                    <a class="nav-link" href="#onas">O nás</a>
                </li>
                <li class="nav-part">
                    <a class="nav-link" href="#kontakt">Kontakt</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="backdrop" :class="isOpened ? 'openedNavigation' : ''"></div>
</header>