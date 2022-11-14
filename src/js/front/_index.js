// Register SW
if (window.location.origin.includes('sandbox') && 'serviceWorker' in navigator) {
    navigator.serviceWorker.register(window.location.origin + '/app/themes/woody-theme/pwa/woody-sw.js', {'scope' : '/'});
}

import 'woody-library/assets/js/_index';
import './framework/foundation-explicit-pieces';
import './plugins/scroll_to_top';
import './plugins/focus';

