document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.jslink').forEach(function(link){
        link.addEventListener('click',function(e){
            e.preventDefault();
            var url = this.getAttribute('data-url');
            var title = this.getAttribute('data-title');
            var cont = document.getElementById('joy_iframe_container');
            // Adapter la taille de l'iframe à la moitié de la largeur et à la hauteur totale de la fenêtre, en gardant une marge
            var margin = 20;
            var width = Math.floor(window.innerWidth / 2) - margin * 2;
            var height = window.innerHeight - margin * 2;
            cont.innerHTML = "<div style='text-align:right;background:#eee;padding:2px;'><button onclick=\"document.getElementById('joy_iframe_container').style.display='none'\">✖</button></div>"+
                "<iframe src='"+url+"' title='"+title+"' style='width:"+width+"px;height:"+height+"px;border:0;'></iframe>";
            cont.style.display = 'block';
            cont.style.top = margin + 'px';
            cont.style.right = margin + 'px';
            cont.style.left = 'auto';
        });
    });
});
