<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorDoctrineUnique validates that the uniqueness of a column.
 *
 * Warning: sfValidatorDoctrineUnique is susceptible to race conditions.
 * To avoid this issue, wrap the validation process and the model saving
 * inside a transaction.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @author     Russell Flynn    <russ@eatmymonkeydust.com>
 * @version    SVN: $Id: sfValidatorDoctrineUnique.class.php 8807 2008-05-06 14:12:28Z fabien $
 */
class sfValidatorDoctrineUnique extends sfValidatorSchema
{
  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em, $options = array(), $messages = array())
  {
    $this->em = $em;
    parent::__construct(null, $options, $messages);
  }

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * model:              The model class (required)
   *  * column:             The unique column name in Doctrine field name format (required)
   *                        If the uniquess is for several columns, you can pass an array of field names
   *  * primary_key:        The primary key column name in Doctrine field name format (optional, will be introspected if not provided)
   *                        You can also pass an array if the table has several primary keys
   *  * connection:         The Doctrine connection to use (null by default)
   *  * throw_global_error: Whether to throw a global error (false by default) or an error tied to the first field related to the column option array
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('model');
    $this->addRequiredOption('column');
    $this->addOption('primary_key', null);
    $this->addOption('connection', null);
    $this->addOption('throw_global_error', false);

    $this->setMessage('invalid', 'An object with the same "%column%" already exists.');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($values)
  {
    $originalValues = $values;

    if (!is_array($this->getOption('column')))
    {
      $this->setOption('column', array($this->getOption('column')));
    }

    //if $values isn't an array, make it one
    if (!is_array($values))
    {
      //use first column for key
      $columns = $this->getOption('column');
      $values = array($columns[0] => $values);
    }

    $qb = $this->em->createQueryBuilder()->select('a')->from($this->getOption('model'), 'a');
    $i  = 0;
    foreach ($this->getOption('column') as $column)
    {
      if (!array_key_exists($column, $values))
      {
        // one of the columns has be removed from the form
        return $originalValues;
      }

      $qb->andWhere('a.' . $column . ' = ?'.++$i);
      $qb->setParameter($i, $values[$column]);
    }

    $object = current($qb->setMaxResults(1)->getQuery()->execute());

    // if no object or if we're updating the object, it's ok
    if (!$object || $this->isUpdate($object, $values))
    {
      return $originalValues;
    }

    $error = new sfValidatorError($this, 'invalid', array('column' => implode(', ', $this->getOption('column'))));

    if ($this->getOption('throw_global_error'))
    {
      throw $error;
    }

    $columns = $this->getOption('column');

    throw new sfValidatorErrorSchema($this, array($columns[0] => $error));
  }

  /**
   * Returns whether the object is being updated.
   *
   * @param object A compatable object
   * @param array  An array of values
   *
   * @param Boolean true if the object is being updated, false otherwise
   */
  protected function isUpdate($object, $values)
  {
    $primaryKeyValArray = $this->em->getClassMetadata($this->getOption("model"))->getIdentifierValues($object);

    // check each primary key column
    foreach (array_keys($primaryKeyValArray) as $column)
    {
      if (!isset($values[$column]) ||  $primaryKeyValArray[$column] != $values[$column])
      {
        return false;
      }
    }
    return true;
  }

  /**
   * Returns the primary keys for the model.
   *
   * @return array An array of primary keys
   */
  protected function getPrimaryKeys()
  {
    if (null === $this->getOption('primary_key'))
    {
      $primaryKeyColumns = $this->em->getClassMetadata($this->getOption("model"))->getIdentifier();

      $this->setOption('primary_key', $primaryKeyColumns);
    }

    if (!is_array($this->getOption('primary_key')))
    {
      $this->setOption('primary_key', array($this->getOption('primary_key')));
    }

    return $this->getOption('primary_key');
  }
}