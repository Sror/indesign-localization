<?php
/**
 * Description of nodes
 *
 * @author MadTechie
 */
require_once 'node.php';
//require_once 'style.php';
class nodes extends node {
  protected $nodes = array();
  protected $current = -1;

  /**NODES**/

  /**
   * getNodes gets all the items ans an array
   * @return array of nodes
   */
  public function getNodes() {
    return $this->nodes;
  }

  /**
   * countNodes countsthe number of nodes
   * @return int total nodes
   */
  public function countNodes() {
    return count($this->nodes);
  }

  /**
   * setNodes overrites all items with a new set
   * @param mixed $nodes
   * @return array of nodes
   */
  public function setNodes($nodes) {
    return $this->nodes = $nodes;
  }

  /**
   * getNode Gets a node
   * @param int $key
   * @return  node
   */
  public function getNode($key) {
    return $this->nodes[$key];
  }

  /**
   * setNode Set a node
   * @param int $key
   * @param node $node
   */
  public function setNode($key, node $node) {
    $this->nodes[$key];
  }

  /**
   * addNode added an items to the end of the list
   * @param mixed $node
   * @return int the new number of elements in the array.
   */
  public function addNode(node $node){
    if($node->isEmpty()) return false;
    return array_push($this->nodes, $node);
  }

  /**
   * NodePop drops the last item
   * @return int the last value of array
   */
  public function NodePop() {
    return array_pop($this->nodes);
  }

  /**
   * Resets the counter
   */
  public function reset() {
    $this->current = -1;
  }

  /**
   * CurrentKey gets the current counter
   * @return counter
   */
  public function CurrentKey() {
    return $this->current;
  }

  /**
   * setItem sets a item
   * @param int $Key
   * @param node $node
   */
  public function setItem($Key, node $node) {
    $this->nodes[$Key] = $node;
  }

  /**
   * Current returns the Current Item
   * @return node
   */
  public function Current() {
    if (count($this->nodes) - 1 >= $this->current) {
      return $this->nodes[$this->current];
    }else {
      trigger_error("nodes Current Item not found",E_USER_ERROR);
    }
  }

  /**
   * NextItem returns the next item or null is no more exist
   * @return mixed (node|null)
   */
  public function NextItem() {
    if ($this->hasNextItem()) {
      return $this->nodes[++$this->current];
    } else {
      $this->reset();
      return null;
    }
  }
  public function PreviousItem() {
    if ($this->hasPrevItem()) {
      return $this->nodes[--$this->current];
    } else {
      $this->reset();
      return null;
    }
  }

  /**
   * hasNextItem check for next item
   * @return bool
   */
  public function hasNextItem() {
    return (count($this->nodes) - 1 > $this->current);
  }
  /**
   * hasPrevItem check for previous item
   * @return bool
   */
  public function hasPrevItem() {
    return ($this->current > -1);
  }

  /**
   * setCurrent sets the current item
   * @param mixed $node
   * @return mixed
   */
  public function setCurrent(node $node) {
    return $this->nodes[$this->current] = $node;
  }

  /**
   * removeCurrent removed the current item and resets the list (current item will be the next item)
   */
  public function removeCurrent() {
    unset($this->nodes[$this->current]);
    $this->nodes = array_values($this->nodes);
    $this->current--;
  }

  public function replaceNode($Key, nodes $Node) {
    return $this->nodes[$Key] = $Node;
  }

  public function removeNode($Key) {
    unset($this->nodes[$Key]);
    $this->nodes = array_values($this->nodes);
  }


  /**MAGIC**/
  public function __clone() {
    foreach ($this as $key => $val) {
      if (is_object($val) || (is_array($val))) {
	$this->{$key} = unserialize(serialize($val));
      }
    }
  }

  /**
   * __toString returns all items as a string
   * @return string
   */
  public function  __toString() {
    $str = '';
    $this->Merge();
    #$str .= parent::__toString();
    $str .= $this->getText();
    foreach($this->nodes as $node){
      $str .= (string)$node;
    }
    $str = $this->toString($str);
    return $str;//implode('', $this->nodes);
  }

  public function isEmpty(){
    if(count($this->nodes)>0) return false;
    return parent::isEmpty();
  }

  public function Merge($pHash='') {
      $Previous_pHash = "";
      $Previous_pKey = "";
      foreach ($this->nodes as $pKey => $node) {
	if($node instanceof nodes) {
	  $node->Merge();
	}elseif($node instanceof node) {
	  $pHash = $node->getStylesHash($node);
	  if($pHash == $Previous_pHash) {
	    $prevNode = $this->getNode($Previous_pKey);
	    $prevNode->appendText($node->hasText()?$node->getText():"");
	    $this->removeNode($pKey);
	  }else {
	    $Previous_pHash = $pHash;
	    $Previous_pKey = $pKey;
	  }
	}
      }
      $this->setNodes(array_values($this->nodes));
    }
    
}