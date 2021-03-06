<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Service
 */

namespace ZendService\Technorati;

use DomElement;
use DOMXPath;

/**
 * Represents a single Technorati Search query result object.
 * It is never returned as a standalone object,
 * but it always belongs to a valid SearchResultSet object.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @abstract
 */
abstract class AbstractResult
{
    /**
     * An associative array of 'fieldName' => 'xmlfieldtag'
     *
     * @var     array
     * @access  protected
     */
    protected $fields;

    /**
     * The ReST fragment for this result object
     *
     * @var     DomElement
     * @access  protected
     */
    protected $dom;

    /**
     * Object for $this->dom
     *
     * @var     DOMXpath
     * @access  protected
     */
    protected $xpath;


    /**
     * Constructs a new object from DOM Element.
     * Properties are automatically fetched from XML
     * according to array of $fields to be read.
     *
     * @param   DomElement $result  the ReST fragment for this object
     */
    public function __construct(DomElement $dom)
    {
        $this->xpath = new DOMXPath($dom->ownerDocument);
        $this->dom = $dom;

        // default fields for all search results
        $fields = array();

        // merge with child's object fields
        $this->fields = array_merge($this->fields, $fields);

        // add results to appropriate fields
        foreach($this->fields as $phpName => $xmlName) {
            $query = "./$xmlName/text()";
            $node = $this->xpath->query($query, $this->dom);
            if ($node->length == 1) {
                $this->{$phpName} = (string) $node->item(0)->data;
            }
        }
    }

    /**
     * Parses weblog node and sets weblog object.
     *
     * @return  void
     */
    protected function parseWeblog()
    {
        // weblog object field
        $result = $this->xpath->query('./weblog', $this->dom);
        if ($result->length == 1) {
            $this->weblog = new Weblog($result->item(0));
        } else {
            $this->weblog = null;
        }
    }

    /**
     * Returns the document fragment for this object as XML string.
     *
     * @return string   the document fragment for this object
     *                  converted into XML format
     */
    public function getXml()
    {
        return $this->dom->ownerDocument->saveXML($this->dom);
    }
}
