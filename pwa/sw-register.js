// Register SW
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(window.location.origin + '/app/themes/woody-theme/pwa/woody-sw.js').then(function(reg) {
        console.log('Registration succeeded. Scope is ' + reg.scope);
    }).catch(function(error) {
        console.log('Registration failed with ' + error);
      });
}
