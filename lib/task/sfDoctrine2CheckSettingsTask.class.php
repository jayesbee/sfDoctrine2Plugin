<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrine2BaseTask.class.php');

/**
 * Check if Doctrine is properly configured for a production environment
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @author     Russell Flynn <russ@eatmymonkeydust.com>
 */
class sfDoctrine2CheckSettings extends sfDoctrine2BaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = 'doctrine2';
    $this->name = 'check-settings';
    $this->briefDescription = 'Checks if Doctrine is properly configured for production environment';

    $this->detailedDescription = <<<EOF
The [doctrine2:check-settings|INFO] task checks if Doctrine is properly
configured for production environment

  [./symfony doctrine2:check-settings|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->callDoctrineCli('orm:ensure-production-settings');
  }
}