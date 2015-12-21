<?php
/* For licensing terms, see /license.txt */

use Chamilo\CoreBundle\Framework\Container;

/**
 * @package chamilo.social
 * @author Julio Montoya <gugli100@gmail.com>
 */

$cidReset = true;

//require_once '../inc/global.inc.php';

//Add the JS needed to use the jqgrid
$htmlHeadXtra[] = api_get_jqgrid_js();

$interbreadcrumb[] = array("url" => "index.php","name" => get_lang('Skills'));

//jqgrid will use this URL to do the selects
$url = api_get_path(WEB_AJAX_PATH).'model.ajax.php?a=get_user_skill_ranking';

//The order is important you need to check the the $column variable in the model.ajax.php file
$columns = array(
    get_lang('Photo'),
    get_lang('FirstName'),
    get_lang('LastName'),
    get_lang('SkillsAcquired'),
    get_lang('CurrentlyLearning'),
    get_lang('Rank')
);

$column_model   = array(
    array(
        'name' => 'photo',
        'index' => 'photo',
        'width' => '10',
        'align' => 'center',
        'sortable' => 'false',
    ),
    array(
        'name' => 'firstname',
        'index' => 'firstname',
        'width' => '70',
        'align' => 'center',
        'sortable' => 'false',
    ),
    array(
        'name' => 'lastname',
        'index' => 'lastname',
        'width' => '70',
        'align' => 'center',
        'sortable' => 'false',
    ),
    array(
        'name' => 'skills_acquired',
        'index' => 'skills_acquired',
        'width' => '30	',
        'align' => 'center',
        'sortable' => 'false',
    ),
    array(
        'name' => 'currently_learning',
        'index' => 'currently_learning',
        'width' => '30',
        'align' => 'center',
        'sortable' => 'false',
    ),
    array(
        'name' => 'rank',
        'index' => 'rank',
        'width' => '30',
        'align' => 'center',
        'sortable' => 'false',
    ),
);

//Autowidth
$extra_params['autowidth'] = 'true';

//height auto
$extra_params['height'] = 'auto';
//$extra_params['excel'] = 'excel';

//$extra_params['rowList'] = array(10, 20 ,30);

$jqgrid = Display::grid_js(
    'skill_ranking',
    $url,
    $columns,
    $column_model,
    $extra_params,
    array(),
    null,
    true
);
$content = Display::grid_html('skill_ranking');

//$tpl = new Template(get_lang('Ranking'));
$tpl = Container::getTwig();
$tpl->addGlobal('jqgrid_html', $jqgrid);
$content .= $tpl->render('@template_style/skill/skill_ranking.html.twig');
echo $content;

