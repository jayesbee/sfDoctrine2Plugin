<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license informationation, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrine2BaseTask.class.php');

/**
 * Create filter form classes for the current model.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfDoctrine2BuildFiltersTask extends sfDoctrine2BaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('model-dir-name', null, sfCommandOption::PARAMETER_REQUIRED, 'The model dir name', 'model'),
      new sfCommandOption('filter-dir-name', null, sfCommandOption::PARAMETER_REQUIRED, 'The filter form dir name', 'filter'),
    ));

    $this->namespace = 'doctrine2';
    $this->name = 'build-filters';
    $this->briefDescription = 'Creates filter form classes for the current model';

    $this->detailedDescription = <<<EOF
The [doctrine2:build-filters|INFO] task creates filter form classes from the schema:

  [./symfony doctrine2:build-filters|INFO]

The task read the schema information in [config/*schema.xml|COMMENT] and/or
[config/*schema.yml|COMMENT] from the project and all installed plugins.

The model filter form classes files are created in [lib/filter|COMMENT].

This task never overrides custom classes in [lib/filter|COMMENT].
It only replaces base classes generated in [lib/filter/base|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('doctrine', 'generating filter form classes');
    $databaseManager = $this->initDBM();
    $generatorManager = new sfGeneratorManager($this->configuration);
    $generatorManager->generate('sfDoctrine2FormFilterGenerator', array(
      'model_dir_name'  => $options['model-dir-name'],
      'filter_dir_name' => $options['filter-dir-name'],
      'database_manager' => $databaseManager,
    ));

    $properties = parse_ini_file(sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'properties.ini', true);

    $constants = array(
      'PROJECT_NAME' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
      'AUTHOR_NAME'  => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here'
    );

    // customize php and yml files
    $finder = sfFinder::type('file')->name('*.php');
    $this->getFilesystem()->replaceTokens($finder->in(sfConfig::get('sf_lib_dir').'/filter/'), '##', '##', $constants);

    $this->reloadAutoload();
  }
}
