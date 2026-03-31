<?php
if(get_current_user_id()) {
$user_stream_filters = PeepSoUser::get_stream_filters();
/*
* 0 == never
* 1 == mobile
* 2 == desktop
* 3 == always
*/
$compact = PeepSo::get_option_new('stream_filters_compact');
$compact_class = [];
if(in_array($compact,[1,3])) {
    $compact_class []= 'pso-posts__filters--compact-mobile'; // @TODO TBD
}

if(in_array($compact,[2,3])) {
    $compact_class []= 'pso-posts__filters--compact-desktop';// @TODO TBD
}

$compact_class = implode(' ', $compact_class);
?>

<div class="pso-posts__filters <?php echo $compact_class;?>" data-ps="filter-forms">
    <div class="pso-posts-filters__start ps-js-activitystream-filters-wrapper">
        <?php 
        /** HIDE MY POSTS **/
        $show_my_posts_list =  apply_filters( 'peepso_show_my_posts_list', [] );

        reset($show_my_posts_list);
        $default = key($show_my_posts_list);

        $show_my_posts = $user_stream_filters['show_my_posts'];

        if(!array_key_exists($show_my_posts, $show_my_posts_list)) {
            $show_my_posts = $default;
        }

        $selected = $show_my_posts_list[$show_my_posts];
        ?>
        <input type="hidden" id="peepso_stream_filter_show_my_posts" value="<?php echo $show_my_posts; ?>" />
        <div class="pso-posts__filter pso-posts__filter--myposts ps-js-dropdown ps-js-activitystream-filter" data-id="peepso_stream_filter_show_my_posts">
            <a href="javascript:" class="pso-posts-filter__toggle ps-js-dropdown-toggle" aria-haspopup="true">
                <i class="<?php echo $selected['icon']; ?>"></i>
                <span><?php echo $selected['label']; ?></span>
            </a>
            <div class="pso-posts-filter__box ps-js-dropdown-menu" role="menu">
                <div class="pso-posts-filter__title">
                    <span><?php echo esc_attr__('My posts', 'peepso-core'); ?></span>
                </div>
                <div class="pso-posts-filter__options">
                    <?php foreach ($show_my_posts_list as $key => $value) { ?>
                        <a class="pso-posts-filter__option" data-option-value="<?php echo $key; ?>" role="menuitem">
                            <div class="pso-posts-filter-option__control">
                                <input type="radio" name="peepso_stream_filter_show_my_posts" id="peepso_stream_filter_show_my_posts_opt_<?php echo $key ?>"
                                       value="<?php echo $key ?>" <?php if($key == $show_my_posts) echo "checked"; ?> />
                            </div>
                            <label class="pso-posts-filter-option__content" for="peepso_stream_filter_show_my_posts_opt_<?php echo $key ?>">
                                <span class="pso-posts-filter-option__name"><?php echo $value['label']; ?></span>
                            </label>
                            <div class="pso-posts-filter-option__icon">
                                <i class="<?php echo $value['icon']; ?>"></i>
                            </div>
                        </a>
                    <?php } ?>
                </div>
                <div class="pso-posts-filter__actions">
                    <button class="pso-btn pso-btn--neutral ps-js-cancel"><?php echo esc_attr__('Cancel', 'peepso-core'); ?></button>
                    <button class="pso-btn pso-btn--primary ps-js-apply" ><?php echo esc_attr__('Apply', 'peepso-core'); ?></button>
                </div>
            </div>
        </div>
        <?php
        $sort_posts =  apply_filters( 'peepso_stream_sort_list', [] );
        $sort_by = PeepSo::get_option_new('stream_sort_default');

        // @todo the default is being ignored here
        if(isset($user_stream_filters['sort_by']) && array_key_exists($user_stream_filters['sort_by'], $sort_posts)) {
            $sort_by = $user_stream_filters['sort_by'];
        }

        $selected = $sort_posts[$sort_by];
        ?>
        <input type="hidden" id="peepso_stream_filter_sort_by" value="<?php echo $sort_by; ?>" />
        <div class="pso-posts__filter pso-posts__filter--sort ps-js-dropdown ps-js-activitystream-filter" data-id="peepso_stream_filter_sort_by">
            <a href="javascript:" class="pso-posts-filter__toggle ps-js-dropdown-toggle" aria-haspopup="true">
                <i class="<?php echo $selected['icon']; ?>"></i>
                <span><?php echo $selected['label']; ?></span>
            </a>
            <div class="pso-posts-filter__box ps-js-dropdown-menu" role="menu">
                <div class="pso-posts-filter__title">
                    <span><?php echo esc_attr__('Sort by', 'peepso-core'); ?></span>
                </div>
                <div class="pso-posts-filter__options">
                    <?php foreach ($sort_posts as $key => $value) { ?>
                        <a class="pso-posts-filter__option" data-option-value="<?php echo $key; ?>" role="menuitem">
                            <div class="pso-posts-filter-option__control">
                                <input type="radio" name="peepso_stream_filter_sort_by" id="peepso_stream_filter_sort_by_opt_<?php echo $key ?>"
                                       value="<?php echo $key ?>" <?php if($key == $sort_by) echo "checked"; ?> />
                            </div>
                            <label class="pso-posts-filter-option__content" for="peepso_stream_filter_sort_by_opt_<?php echo $key ?>">
                                <span class="pso-posts-filter-option__name"><?php echo $value['label']; ?></span>
                            </label>
                            <div class="pso-posts-filter-option__icon">
                                <i class="<?php echo $value['icon']; ?>"></i>
                            </div>
                        </a>
                    <?php } ?>
                </div>
                <div class="pso-posts-filter__actions">
                    <button class="pso-btn pso-btn--neutral ps-js-cancel"><?php echo esc_attr__('Cancel', 'peepso-core'); ?></button>
                    <button class="pso-btn pso-btn--primary ps-js-apply" ><?php echo esc_attr__('Apply', 'peepso-core'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="pso-posts-filters__end">
        <?php

        /** SEARCH POSTS **/
        $search = FALSE;
        $PeepSoUrlSegments = PeepSoUrlSegments::get_instance();

        #4158 ?search/querystring does not work with special chars
        if('search' == $PeepSoUrlSegments->get(1)) {
            $search = $PeepSoUrlSegments->get(2);
        }

        #4158 ?search/querystring does not work with special chars
        if(isset($_GET['filter'])) {
            $PeepSoInput = new PeepSoInput();
            $search = $PeepSoInput->value('filter', '', FALSE);
        }

        #7602 XSS
        if($search!==FALSE) $search = str_replace(['<','>','&gt;','&lt;'], ' ', $search);
        if(!strlen($search)) $search = FALSE;
        ?>
        <input type="hidden" id="peepso_search" value="exact" />
        <!-- Search -->
        <div class="pso-posts__filter pso-posts__filter--search ps-js-dropdown ps-js-activitystream-filter" data-id="peepso_search">
            <a href="#" class="pso-posts-filter__toggle pso-tip pso-tip--top ps-js-dropdown-toggle" aria-haspopup="true" aria-label="<?php echo esc_attr__('Search', 'peepso-core'); ?>">
                <i class="pso-i-search"></i>
            </a>
            <div class="pso-posts-filter__box ps-js-dropdown-menu" role="menu">
                <div class="pso-posts-filter__search">
                    <i class="pso-i-search"></i>
                    <input type="text" id="ps-activitystream-search" class="pso-input--reset pso-posts-filter-search__input"
                        placeholder="<?php echo esc_attr__('Type to search', 'peepso-core'); ?>" value="<?php echo $search;?>" />
                </div>
                <div class="pso-posts-filter__options">
                    <a class="pso-posts-filter__option" data-option-value="exact" role="menuitem">
                        <div class="pso-posts-filter-option__control">
                            <input type="radio" name="peepso_search" id="peepso_search_opt_exact" value="exact" checked />
                        </div>
                        <label class="pso-posts-filter-option__content" for="peepso_search_opt_exact">
                            <span class="pso-posts-filter-option__name"><?php echo esc_attr__('Exact phrase', 'peepso-core'); ?></span>
                        </label>
                    </a>
                    <a class="pso-posts-filter__option" data-option-value="any" role="menuitem">
                        <div class="pso-posts-filter-option__control">
                            <input type="radio" name="peepso_search" id="peepso_search_opt_any" value="any" />
                        </div>
                        <label class="pso-posts-filter-option__content" for="peepso_search_opt_any">
                            <span class="pso-posts-filter-option__name"><?php echo esc_attr__('Any of the words', 'peepso-core'); ?></span>
                        </label>
                    </a>
                </div>
                <div class="pso-posts-filter__actions">
                    <button class="pso-btn pso-btn--neutral ps-js-cancel"><?php echo esc_attr__('Cancel', 'peepso-core'); ?></button>
                    <button class="pso-btn pso-btn--primary ps-js-search" ><?php echo esc_attr__('Search', 'peepso-core'); ?></button>
                </div>
            </div>
            <span data-empty="<?php //echo esc_attr__('Search', 'peepso-core'); ?>"
                      data-keyword="<?php echo esc_attr__('Search: ', 'peepso-core'); ?>"></span>
        </div>

        <?php
        /** ADDITIONAL FILTERS - HOOKABLE **/
        do_action('peepso_action_render_stream_filters');
        ?>
    </div>
</div>
<div class="pso-posts-filters__active" data-ps="filter-active" style="display:none">
    <div class="pso-posts-filters-active__item" data-ps="filter-active-search" style="display:none">
        <span class="pso-posts-filters-active__label"><i class="pso-i-search-alt"></i></span>
        <span class="pso-posts-filters-active__value" data-text></span>
        <a href="#" class="pso-posts-filters-active__close pso-tip pso-tip--top" data-remove
            aria-label="<?php echo esc_attr__('Remove search filter', 'peepso-core'); ?>"><i class="pso-i-cross-small"></i></a>
    </div>
    <div class="pso-posts-filters-active__item" data-ps="filter-active-hashtag" style="display:none">
        <span class="pso-posts-filters-active__label"><i class="pso-i-hashtag"></i></span>
        <span class="pso-posts-filters-active__value" data-text></span>
        <a href="#" class="pso-posts-filters-active__close pso-tip pso-tip--top" data-remove
            aria-label="<?php echo esc_attr__('Remove hashtag filter', 'peepso-core'); ?>"><i class="pso-i-cross-small"></i></a>
    </div>
</div>
<div id="ps-stream__filters-warning" class="ps-posts__filters-warning ps-posts__empty" data-ps="filter-warning" style="display:none">
    <i class="gcis gci-info-circle"></i> <?php echo esc_attr__('You are currently only viewing %s content.','peepso-core'); ?>
</div>

<?php }
