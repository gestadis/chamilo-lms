<?php

/* For licensing terms, see /license.txt */

use Chamilo\CoreBundle\Framework\Container;
use Chamilo\CourseBundle\Entity\CQuizQuestion;
use ChamiloSession as Session;

/**
 * This file generates the ActionScript variables code used by the HotSpot .swf.
 *
 *
 * @author Toon Keppens
 */
require_once __DIR__.'/../inc/global.inc.php';

api_protect_course_script(false);

$isAllowedToEdit = api_is_allowed_to_edit(null, true);

if (!$isAllowedToEdit) {
    api_not_allowed(true);
    exit;
}

$_course = api_get_course_info();
$questionId = isset($_GET['modifyAnswers']) ? (int) $_GET['modifyAnswers'] : 0;
$questionRepo = Container::getQuestionRepository();
/** @var CQuizQuestion $objQuestion */
$objQuestion = $questionRepo->find($questionId);
$resourceFile = $objQuestion->getResourceNode()->getResourceFile();

$pictureWidth = $resourceFile->getWidth();
$pictureHeight = $resourceFile->getHeight();
$imagePath = $questionRepo->getHotSpotImageUrl($objQuestion);

$data = [];
$data['type'] = 'admin';
$data['lang'] = [
    'Square' => get_lang('Square'),
    'Ellipse' => get_lang('Ellipse'),
    'Polygon' => get_lang('Polygon'),
    'HotspotStatus1' => get_lang('HotspotStatus1'),
    'HotspotStatus2Polygon' => get_lang('HotspotStatus2Polygon'),
    'HotspotStatus2Other' => get_lang('HotspotStatus2Other'),
    'HotspotStatus3' => get_lang('HotspotStatus3'),
    'HotspotShowUserPoints' => get_lang('HotspotShowUserPoints'),
    'ShowHotspots' => get_lang('ShowHotspots'),
    'Triesleft' => get_lang('Triesleft'),
    'HotspotExerciseFinished' => get_lang('HotspotExerciseFinished'),
    'NextAnswer' => get_lang('NextAnswer'),
    'Delineation' => get_lang('Delineation'),
    'CloseDelineation' => get_lang('CloseDelineation'),
    'Oar' => get_lang('Oar'),
    'ClosePolygon' => get_lang('ClosePolygon'),
    'DelineationStatus1' => get_lang('DelineationStatus1'),
];
$data['image'] = $imagePath;
$data['image_width'] = $pictureWidth;
$data['image_height'] = $pictureHeight;
$data['courseCode'] = $_course['path'];
$data['hotspots'] = [];

$i = 0;
$nmbrTries = 0;
$answer_type = $objQuestion->getType();
$answers = Session::read('tmp_answers');
$nbrAnswers = count($answers['answer']);

for ($i = 1; $i <= $nbrAnswers; ++$i) {
    $hotSpot = [];
    $hotSpot['id'] = null;
    $hotSpot['answer'] = $answers['answer'][$i];

    if (HOT_SPOT_DELINEATION == $answer_type) {
        if (1 == $i) {
            $hotSpot['type'] = 'delineation';
        } else {
            $hotSpot['type'] = 'oar';
        }
    } else {
        // Square or rectancle
        if ('square' == $answers['hotspot_type'][$i]) {
            $hotSpot['type'] = 'square';
        }

        // Circle or ovale
        if ('circle' == $answers['hotspot_type'][$i]) {
            $hotSpot['type'] = 'circle';
        }

        // Polygon
        if ('poly' == $answers['hotspot_type'][$i]) {
            $hotSpot['type'] = 'poly';
        }
        /*// Delineation
        if ($answers['hotspot_type'][$i] == 'delineation')
        {
            $output .= "&hotspot_".$i."_type=delineation";
        }*/
    }

    // This is a good answer, count + 1 for nmbr of clicks
    if ($answers['weighting'][$i] > 0) {
        ++$nmbrTries;
    }

    $hotSpot['coord'] = $answers['hotspot_coordinates'][$i];
    $data['hotspots'][] = $hotSpot;
}

// Output
$data['nmbrTries'] = $nmbrTries;
$data['done'] = 'done';

header('Content-Type: application/json');

echo json_encode($data);
