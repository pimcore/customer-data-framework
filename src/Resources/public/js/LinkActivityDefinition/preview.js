$(function(){

   var clipboard = new Clipboard('.js-copy-to-clipboard');

    clipboard.on('success', function(e) {
        alert('copied');
    });

});