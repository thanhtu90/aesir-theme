jQuery(function($){
    let page = loadmore_params.current_page;
    let max_page = loadmore_params.max_page;
    let loading = false;

    function loadMore() {
        if (loading || page >= max_page) return;
        loading = true;
        page++;

        $.ajax({
            url: loadmore_params.ajaxurl,
            type: 'POST',
            data: {
                action: 'loadmore',
                query: loadmore_params.query,
                page: page
            },
            beforeSend: function() {
                $('#product-grid').after('<div id="infinite-loading" class="text-center py-6">Loading...</div>');
            },
            success: function(data){
                if(data){
                    $('#product-grid').append(data);
                }
                $('#infinite-loading').remove();
                loading = false;
            }
        });
    }

    // Trigger when scroll near bottom
    $(window).scroll(function(){
        if ( $(window).scrollTop() + $(window).height() + 200 >= $(document).height() ) {
            loadMore();
        }
    });
});
