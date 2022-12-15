// Register SW
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/woody-sw.js').then(function(reg) {
    }).catch(function(error) {
        console.log('Registration failed with ' + error);
      });
}
