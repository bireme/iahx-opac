$(document).ready(
    function(){
        $("div.abstract > a, div.tags > a").each(
        function(){
            var descriptor = $(this).html();
            var element_id = $(this).attr('id');
            var parent_div = $(this).parent();
            var parent_id  = parent_div.attr('id');

            var descriptor_text=descriptor.replace(/\/.*/,'');
            var descriptor_param=descriptor_text.replace(/ /g,'+');
            this.title=descriptor_text;

            if (parent_div.prop('lang')){
                lang = parent_div.prop('lang');
            }else{
                lang = document.searchForm.lang.value;
            }
            var service_url = SEARCH_URL + "decs-tooltip/"+lang+"/?term="+encodeURI(descriptor_param);

            new jBox('Tooltip', {
              attach: "#" + parent_id + " #" + element_id,
              width: 560,
              responsiveWidth: true,
              title: this.title,
              closeOnMouseleave: true,
              closeButton: true,
              delayOpen: 1000, // set delay before open to avoid false calls
              ajax: {
                url: service_url,
                reload: 'strict',
                setContent: false,
                success: function (response) {
                  this.setContent(response);
                },
                error: function () {
                  this.setContent('Error loading content');
                }
              }
            });
        }
    );
});
