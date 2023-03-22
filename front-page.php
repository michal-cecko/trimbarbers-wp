<?php /* Template Name: Domovská stránka */ ?>
<?php get_header(); ?>
<?php get_template_part("template_parts/reservation", "") ?>


<!--   INTRO SECTION ----- START    -->

<section id="introSection">
    <div class="media-bg-wrapper">
        <?php if ($media = get_field("home-intro-bg_medium")) : ?>
            <?php if (in_array($media['subtype'], ['mp4', 'avi', 'mov'])) : ?>
                <video width="100%" height="100%" muted autoplay>
                    <source src="<?= $media['url'] ?>" type="<?= $media['mime_type'] ?>">
                    Váš prehliadač nepodporuje toto video.
                </video>
            <?php else : ?>
                <img src="<?= $media['url'] ?>" alt="Obrázok na pozadí">
            <?php endif ?>
        <?php endif ?>
    </div>
    <div class="container content-container">
        <?= svgIcon(image_path(false) . "/logo-emblem.svg", ['class' => ['intro-logo']]) ?>
        <?php if ($text = get_field("home-intro-text")) : ?>
            <p class="intro-text"><?= $text ?></p>
        <?php endif ?>
    </div>
</section>

<!--   INTRO SECTION ----- END    -->


<!--   SLUZBY SECTION ----- START    -->

<?php
$services = new WP_query([
    'post_type' => 'service',
    'post_status' => 'publish'
])
?>

<section id="servicesSection">
    <div class="anchor" id="sluzby"></div>
    <div class="container">
        <div class="text-container row">
            <div class="col-md-6">
                <p class="handwritten">
                    <?= get_field("home-services-handwritten") ?>
                </p>
            </div>
            <div class="heading-container col-md-6">
                <h1 class="heading"><?= get_field("home-services-heading") ?></h1>
                <p class="text"><?= get_field("home-services-text") ?></p>
            </div>
        </div>
        <div class="services-container row">
            <div class="col-md-8 services">
                <?php if ($services->have_posts()) : ?>
                    <?php while ($services->have_posts()) : $services->the_post() ?>
                        <div class="service">
                            <div class="name"><?= get_the_title() ?></div>
                            <div class="description text"><?= get_field("serv-description") ?></div>
                            <div class="price"><?= get_field("serv-price") ?>€</div>
                        </div>
                    <?php endwhile ?>
                    <?php wp_reset_postdata(); endif ?>
            </div>
            <div class="col-md-4 image">
                <?php if ($img = get_field("home-services-image")) : ?>
                    <div class="img-container">
                        <img src="<?= $img ?>" alt="">
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>
</section>

<!--   SLUZBY SECTION ----- END    -->


<!--   INSTAGRAM SECTION ----- START    -->

<?php
$address = get_field("socials", "options");
$btnText = get_field("home-ig-btn-text");
$gallery = get_field("home-ig-slider");

$showIGFeed = empty($gallery);
if ($showIGFeed) $gallery = [];
?>

<section id="instagramSection">
    <div class="anchor" id="onas"></div>
    <div class="container">
        <div class="text-container">
            <div class="heading-container">
                <h2 class="heading white"><?= get_field("home-ig-heading-1") ?></h2>
                <?php if ($head2 = get_field("home-ig-heading-2")) : ?>
                    <h2 class="heading white"><?= $head2 ?></h2>
                <?php endif ?>
            </div>
            <div class="handwritten"><?= get_field("home-ig-handwritten") ?></div>
            <?php if ($btnText && !empty($address['instagram'])) : ?>
                <a href="<?= $address['instagram'] ?>" class="btn btn-primary btn-solid"><?= $btnText ?></a>
            <?php endif ?>
        </div>
        <div class="swiper-gallery-container row">
            <div class="col-md-3">
                <div class="swiper-arrows">
                    <div class="arrow prev">
                        <?= svgIcon(icon_path(false) . "/icon-arrow.svg") ?>
                    </div>
                    <div class="arrow next">
                        <?= svgIcon(icon_path(false) . "/icon-arrow.svg") ?>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="swiper swiper-gallery">
                    <div class="swiper-wrapper">
                        <?php foreach ($gallery as $image) : ?>
                            <?php
                            if(!$showIGFeed) $imgUrl = $image['url'];
                            else $imgUrl = get_template_directory_uri() . "instagram/posts/" . $image['url'];
                            ?>
                            <div class="swiper-slide">
                                <a <?= $showIGFeed ? 'href="' . $address['instagram'] . '"' : "" ?> class="img-container">
                                    <img src="<?= $imgUrl ?>" alt="">
                                </a>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!--   INSTAGRAM SECTION ----- END    -->




<!--   MAP SECTION ----- START    -->

<section id="mapSection">
    <div class="container">
        <div class="row">
            <div class="col-md-6 order-md-0 order-1 map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2606.1419796915507!2d18.736092732742925!3d49.21683441723154!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47145eafb6cf9b1d%3A0xbd6f24207050c59!2s48a%2C%20Antona%20Bernol%C3%A1ka%208316%2C%20010%2001%20%C5%BDilina!5e0!3m2!1ssk!2ssk!4v1678007582730!5m2!1ssk!2ssk" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="col-md-6 order-md-1 order-0 info">
                <div class="heading-container">
                    <h3 class="heading"><?= get_field("home-map-heading-1") ?></h3>
                    <?php if ($head2 = get_field("home-map-heading-2")) : ?>
                        <h3 class="heading"><?= $head2 ?></h3>
                    <?php endif ?>
                </div>
                <div class="address-container">
                    <p class="text"><?= get_field("address", "options") ?></p>
                    <a target="_blank" href="https://www.google.com/maps/dir//Bernol%C3%A1kova+ul.+8316%2F48A,+010+01+%C5%BDilina,+Slovakia" class="btn btn-secondary btn-outline">Ukáž trasu</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!--   MAP SECTION ----- END    -->

<div class="notification-container">
    <?php if (isset($_GET['c']) && $_GET['c'] == "1") showNotification("Vaša rezervácia bola úspešne zrušená.", "success") ?>
</div>

<?php get_footer(); ?>
