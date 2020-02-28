(function(body) {
    class Focus {
        constructor() {
            this.usingMouse;
            this.bindEvents();
        }

        bindEvents() {
            // Focus events
            body.addEventListener('focusin', this.addFocus);
            body.addEventListener('focusout', this.removeFocus);

            // Keyboard events
            body.addEventListener('keydown', this.preFocus);

            // Mouse events
            body.addEventListener('mousedown', this.preFocus);

            // Touch events
            body.addEventListener('touchstart', this.addFocus);
            body.addEventListener('touchend', this.removeFocus);
        };

        preFocus(event) {
            this.usingMouse = (event.type === 'mousedown');
        }

        addFocus(event) {
            if (this.usingMouse) {
                event.target.classList.add('focus-mouse');
            }
        };

        removeFocus(event) {
            event.target.classList.remove('focus-mouse');
        };
    }

    const trackFocus = new Focus();

})(document.body);
