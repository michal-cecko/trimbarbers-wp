console.log("General JS has been loaded.")

class General{
    constructor() {
        this._prepareSwipers()
        this._prepareParametersRemoval()
        this._prepareNotifications()
    }

    _prepareSwipers() {
        let _this = this;

        let swiperGallerySelector = '.swiper-gallery'
        let gallery = document.querySelector(swiperGallerySelector);

        const swiperGallery = new Swiper(swiperGallerySelector, {
            direction: 'horizontal',
            loop: false,
            slidesPerView: 1,
            spaceBetween: 16,
            navigation: {
                nextEl: '.swiper-gallery-container .next',
                prevEl: '.swiper-gallery-container .prev'
            },
            breakpoints: {
                560: {
                    slidesPerView: 1.7,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2.3,
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 2.8,
                    spaceBetween: 40,
                },
                1200: {
                    slidesPerView: 3.3,
                    spaceBetween: 40,
                },
                1440: {
                    slidesPerView: 3.6,
                    spaceBetween: 40,
                },
                1600: {
                    slidesPerView: 4.2,
                    spaceBetween: 40,
                },
            }
        });
    }

    _prepareParametersRemoval() {
        if (window.location.search.includes("c=")) {
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            params.delete("c");
            url.search = params.toString();
            window.location.replace(url.toString());
        }
    }

    _prepareNotifications() {
        let notifications = document.querySelectorAll(".notification")
        if(notifications.length) {
            let x = 2200;
            let i = 0;
            notifications.forEach(notification => {
                setTimeout(function () {
                    notification.remove()
                    console.log("removed")
                }, x + (300 * i))
                i++;
            })
        }
    }
}

new General()

//export {}