<div class="container-fluid">
    <div class="col-sm-12 riddle-landingpage-box">

        <div class="riddle-landingpage-box-top">
            <img src="https://i1.wp.com/consciouscat.net/wp-content/uploads/2019/11/black-cat-depositphotos.jpg?fit=600%2C501&ssl=1" alt="Cute NFL cat" style="width: 100%">
        </div>
        <div class="riddle-landingpage-box-bottom">

            <?php if ($data['renderer']->hasData()): ?>
                <h3>
                    <span class="leaderboard-module-gray-heading">Your Score:</span> 
                    <span class="leaderboard-module-score bold"><?php echo $data['renderer']->get('resultData.scorePercentage'); ?>%</span>
                </h3>
            <?php endif; ?>

            <?php echo $data['renderer']->renderModule('leaderboard'); ?>

        </div>

    </div>
</div>