<?php

// Define the template without <tr> so that we can add special classes to the row in the next step
// you can add the parameters "encrypted" and "encryptedEmail" if you do not want to show personal sensitive data. It's important if you want to meet GDPR standards
$template = 
    '<td>{ index }</td>
     <td>{ lead2.Nickname.value }</td>
     <td>{ resultData.scorePercentage }%</td>';

$spotTemplate = '<tr class="leaderboard-spot-td">' . $template . '</tr>';
$template = '<tr>' . $template . '</tr>';

$filler =
    '<tr>
        <td>...</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>';
?>

<!-- the following block will only be rendered, if the user is better than the min variable -->
<p class="leaderboard-module-better-than-heading">
    <?php echo $data['module']->renderShortcode('better-than', [
        'min' => 20,
        'template' => 'Party! Better than %%PERCENTAGE%%%!',
    ]); ?>
</p>

<p class="bold light-gray leaderboard-module-gray-heading">LEADERBOARD</p>
<p class="leadeboard-module-gray-text">
    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore.
</p>

<table class="table table-striped table-responsive">
    <tr class="leaderboard-tr-head">
        <th>#</th>
        <th>Nickname</th>
        <th>Score</th>
    </tr>

    <!-- Here you can set how many entries you want to show on the leaderboard.
        Range can be a number. The code below shows the range from 1-5 
        -->
    <?php echo $data['module']->renderBlock('leaderboard-leads', [
        'range' => [1, 5],
        'template' => $template,
        'spotTemplate' => $spotTemplate,
    ]); ?>

    <!-- Here you can set how many leading entries you want to show on top of the leaderboard.
        Range can be a number. The code below shows the range from 1-5 
        -->
    <?php echo $data['module']->renderBlock('spot-leaderboard-leads', [
        'range' => [1, 1],
        'template' => $template,
        'spotTemplate' => $spotTemplate,
        'templatePrefix' => $filler,
    ]); ?>

    <?php echo $data['module']->renderBlock('last-leaderboard-lead', [
        'template' => $template,
        'templatePrefix' => $filler,
        'spotTemplate' => $spotTemplate,
    ]); ?>
</table>

<p class="leadeboard-module-gray-text">
    <?php echo $data['module']->renderShortcode('placement', [
        'min' => 20,
    ]); ?>
</p>

<!-- 
    place = what's the place the user has missed?
    placeName = specify how the place range should be named (e.g. "You've missed out on the 'top 10' by xxx")
-->
<p class="leadeboard-module-gray-text">
    <?php echo $data['module']->renderShortcode('missed-place', [
        'place' => 10,
        'placeName' => 'top 10',
    ]); ?>
</p>