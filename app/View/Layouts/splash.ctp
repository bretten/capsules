<!DOCTYPE html>
<html>
<head>
    <?php echo $this->Html->charset(); ?>
    <title>
        <?php echo Configure::read('Layout.Title'); ?>
    </title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/splash.css" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <?php
        echo $this->Html->meta('icon');

        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->fetch('script');
    ?>
</head>
<body>
    <div id="splash" class="splash-container">
        <div class="splash-cell">
            <div class="splash-main">

                <div id="splash-header" class="splash-box">
                    <div id="splash-title">
                        <span class="glyphicon glyphicon-map-marker"></span> Capsules
                    </div>
                </div>

                <div id="splash-content" class="splash-box">
                    <?php echo $this->fetch('content'); ?>
                </div>

                <div id="splash-footer" class="text-center">
                    <small>
                        Copyright &copy; <?php echo date("Y"); ?> Brett Namba.  All Rights Reserved.
                    </small>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
