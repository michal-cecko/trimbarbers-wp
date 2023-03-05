## Streetworkout Slovakia
WordPress + PHP | JS + jQuery | Gulp | Sass

---

### Spustenie / Kompilácia / Inštalácia

---

Prvotné skompilovanie assetov:
1. ```npm install```
2. ```gulp production```

Watch assetov
1. ```gulp assets:watch```

Build na produkciu
1. ```gulp production```
2. Build sa nachádza v **/dist** adresári

---

### Wordpress + PHP na backend-e

---

**functions.php** - Zoskupenie všetkých functions súborov dokopy, ktoré sa nachádzajú v priečinku /functions

**functions_theme.php** - Enqueue assetov a ich verziovanie, všeobecné akcie a filtre, theme cleanup od WP zbytočných linkov a scriptov

**functions_posttypes.php** - Registrácia nových post typov a všetky funkcie spojené s nimi

**functions_plugins.php** - Funkcie spojené s pluginmi

**functions_newsletter.php** - Funkcionalita newslettera

**functions_helper.php** - Helper funkcie používané na viacerých miestach

**functions_email.php** - Funkcie spojené s odosielaním emailov z formulárov

**functions_ajax.php** - WP AJAX callbacky

**functions_acf.php** - Registrácia ACF options pages, populatovanie ACF Selectov

---

### WP šablóny, stránky a časti šablón

---

**header.php** + **footer.php** - header a footer

**front-page.php** - Domovská stránka

**template-example.php** - Šáblona špecifickej stránky

**404.php** - 404 page

/**template_parts** - Priečinok so všetkými časťami šablóny na prepoužívanie

---

### Assety - Štýly (Sass)

---

Štýly sa nachádzajú v priečinku */assets/sass*.

>**#1 IMPORTANT**: Pre zachovanie budúceho čítania kódu niekym iným, dodržiavajte vytvorenú štruktúru štýlov!

>**#2 IMPORTANT**: Pre všetky typy selectorov (trieda, id, data atribúty...), ktoré nechcete mať vymazané pri kompilácii pluginom unCSS označte komentárom nasledovne:
>
> ```
> /* uncss:ignore */
> .selector{
>   font-weight: regular;
> }
>```

---

### Assety - Scripty (TS + VueJS)

---

Scripty sa nachádzajú v priečinku */assets/js*.

Štruktúra:

* /components - VueJS komponenty
* /libs - JS knižnice
* general.js - Bežne používané scripty

---

Made by © Synapps