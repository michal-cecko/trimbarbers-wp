import Ajax from "./ajax.js"

class Header extends Ajax {

    constructor() {
        super();
        this.init();
    }

    init() {
        let _Vue = Vue

        new _Vue({
            el: '#header',
            data: {
                isOpened: false,
                scrollPosition: 0,
            },
            created() {
                this.scrollPosition = window.scrollY
                console.log(`Header Vue component has been created.`)
            },
            mounted() {
                window.addEventListener('scroll', this.handleScroll)
            },
            methods: {
                handleScroll() {
                    this.scrollPosition = window.scrollY
                }
            },
            computed: {
                hasStickyHeader() {
                    return this.scrollPosition > 50
                }
            },
        });
    }
}

new Header()

export {}
