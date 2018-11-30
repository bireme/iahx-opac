$(document).ready(
    function(){
        $("div.abstract > a, div.tags > a").each(
        function(){
            var descriptor=$(this).html();
            parent_div = $(this).parent();

            descriptor=descriptor.replace(/\/.*/,'');
            this.title=descriptor;

            if (parent_div.prop('lang')){
                lang = parent_div.prop('lang');
            }else{
                lang = document.searchForm.lang.value;
            }
            this.rel= SEARCH_URL + "decs/"+lang+"/"+escape(descriptor);

            $(this).cluetip(
                {
                    hoverClass:'highlight',
                    sticky:true,
                    closePosition:'title',
                    closeText:'<img src="' + STATIC_URL + 'image/common/gtk-close.png" alt="close" />',
                    fx: {
                        open: 'fadeIn',
                        openSpeed: 'slow'
                    },
                    hoverIntent: {
                      sensitivity:  1,
                      interval:     500,
                      timeout:      0
                    }
                }
            );
        }
    );
});
