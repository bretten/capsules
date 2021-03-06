<?php
/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo $this->Html->charset(); ?>
    <title>
        <?php echo $cakeDescription ?>:
        <?php echo $title_for_layout; ?>
    </title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/style.css" />
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
    <div id="container">
        <nav class="navbar navbar-default" role="navigation">
            <div class="container-fluid">

                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-header-collapse">
                        <span class="sr-only">Toggle Nav</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?php echo Router::url(array('controller' => 'pages', 'action' => 'display', 'home')); ?>">
                        <span class="glyphicon glyphicon-map-marker"></span> Capsules
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="navbar-header-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <?php if (AuthComponent::user()) : ?>
                            <?php
                            // Determine which link is active
                            $isCapsules =
                                $this->here == Router::url(array('controller' => 'capsules', 'action' => 'index'));
                            $isDiscoveries =
                                $this->here == Router::url(array('controller' => 'discoveries', 'action' => 'index'));
                            $isBury = $this->here == Router::url(array('controller' => 'capsules', 'action' => 'add'));
                            $isFind = $this->here == Router::url(array('controller' => 'capsules', 'action' => 'map'));
                            $isAccount =
                                $this->here == Router::url(array('controller' => 'users', 'action' => 'account'));
                            ?>
                        <li<?= $isCapsules ? ' class="active"' : ''; ?>><?php echo $this->Html->link(__('Capsules'), array('controller' => 'capsules', 'action' => 'index')); ?></li>
                        <li<?= $isDiscoveries ? ' class="active"' : ''; ?>><?php echo $this->Html->link(__('Discoveries'), array('controller' => 'discoveries', 'action' => 'index')); ?></li>
                        <li<?= $isBury ? ' class="active"' : ''; ?>><?php echo $this->Html->link(__('Bury a Capsule'), array('controller' => 'capsules', 'action' => 'add')); ?></li>
                        <li<?= $isFind ? ' class="active"' : ''; ?>><?php echo $this->Html->link(__('Find a Capsule'), array('controller' => 'capsules', 'action' => 'map')); ?></li>
                        <li class="dropdown<?= $isAccount ? ' active' : ''; ?>">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo AuthComponent::user('username'); ?> <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li<?= $isAccount ? ' class="active"' : ''; ?>><?php echo $this->Html->link(__('Account'), array('controller' => 'users', 'action' => 'account')); ?></li>
                                <li class="divider"></li>
                                <li><?php echo $this->Html->link(__('Logout'), array('controller' => 'users', 'action' => 'logout')); ?></li>
                            </ul>
                        </li>
                        <?php else : ?>
                        <li><?php echo $this->Html->link(__('Login'), array('controller' => 'users', 'action' => 'login')); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>
        </nav>
        <div id="content">
            <div class="container">
                <?php echo $this->Session->flash(); ?>
                <?php echo $this->fetch('content'); ?>
            </div>
        </div>
    </div>
    <div id="footer" class="text-center">
        <small>
            Copyright &copy; <?php echo date("Y"); ?> Brett Namba.  All Rights Reserved.
        </small>
        <?php echo $this->element('sql_dump'); ?>
    </div>
</body>
</html>
