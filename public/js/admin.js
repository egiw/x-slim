$.pjax.defaults.timeout = 0;
$.pjax.defaults.enabled = false;
$(document)
        .pjax('a[data-pjax]', '#page-wrapper')
        .on('submit', 'form[data-pjax]', function(e) {
            $.pjax.submit(e, "#page-wrapper", {
                data: new FormData(e.currentTarget),
                type: $(e.target).attr("method") || "get",
                processData: false,
                contentType: false,
                cache: false,
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function(e) {
                            var percentage = event.loaded / event.total * 100;
                            console.log(percentage);
                        }, false);
                    }
                    return xhr;
                }
            });
            e.stopPropagation();
            return false;
        })
        .on("pjax:success", function(e, result, status, xhr) {

        });