$(function () {
    if (!$('meta[name="current-showcase-id"]').size()) {
        return;
    }

    Echo.private('App.Admin.Showcase.' + $('meta[name=current-showcase-id]').attr('content'))
        .listen('ReviewsStatsWasChanged', function (e) {
            let stat_reviews_new = e.reviewStats.stat_reviews_new;

            $('.showcase-stat-reviews-new').text(stat_reviews_new);

            if (stat_reviews_new > 0) {
                $('.showcase-stat-reviews-new').closest('.label').removeClass('hidden');
            }
            else {
                $('.showcase-stat-reviews-new').closest('.label').addClass('hidden');
            }
        });

    Echo.private('App.Admin.Showcase.' + $('meta[name=current-showcase-id]').attr('content'))
        .listen('CreatorTemplatesStatsWasChanged', function (e) {
            let stat_templates_new = e.templateStats.stat_templates_new;

            $('.showcase-stat-templates-new').text(stat_templates_new);

            if (stat_templates_new > 0) {
                $('.showcase-stat-templates-new').closest('.label').removeClass('hidden');
            }
            else {
                $('.showcase-stat-templates-new').closest('.label').addClass('hidden');
            }
        });
});
