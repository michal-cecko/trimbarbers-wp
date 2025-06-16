<footer>
    <div class="anchor" id="kontakt"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-10 order-1 order-md-0 row links">
                <div class="col-md-6 contact">
                    <div class="part">
                        <div class="bolded">Napíšte nám</div>
                        <div class="values">
                            <div class="value"><?= get_field("email", "options") ?></div>
                        </div>
                    </div>
                    <span class="alebo">alebo</span>
                    <div class="part">
                        <div class="bolded">Nám zavolajte</div>
                        <div class="values">
                            <div class="value"><?= get_field("phone", "options") ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 socials">
                    <div class="part">
                        <div class="bolded">Sledujte nás</div>
                        <div class="values">
                            <?php if ($IG = get_field("socials_instagram", "options")) : ?>
                                <a href="<?= $IG ?>" class="value">Instagram</a>
                            <?php endif ?>
                            <?php if ($FB = get_field("socials_facebook", "options")) : ?>
                                <a href="<?= $FB ?>" class="value">Facebook</a>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 copyright">
                    2023 © 3M Barbers. Všetky práva vyhradené. Made by <a style="color: #ccc" href="https://synapps.sk">Synapps</a>
                </div>
            </div>
            <div class="col-md-2 order-0 order-md-1 logo">
                <?= svgIcon(image_path(false) . "/logo-emblem.svg") ?>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

</body>
</html>