document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.jslink').forEach(function(link){
        link.addEventListener('click',function(e){
            e.preventDefault();
            var url = this.getAttribute('data-url');
            var title = this.getAttribute('data-title');
            var cont = document.getElementById('joy_iframe_container');
            cont.innerHTML = "<div style='text-align:right;background:#eee;padding:2px;'><button onclick=\"document.getElementById('joy_iframe_container').style.display='none'\">✖</button></div>"+
                "<iframe src='"+url+"' title='"+title+"' style='width:600px;height:500px;border:0;'></iframe>";
            cont.style.display = 'block';
            cont.style.top = '20px';
            cont.style.left = 'auto';
            cont.style.right = '20px';
        });
    });
});
