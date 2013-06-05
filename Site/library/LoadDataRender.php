<?php
/**
 * Class for convert data tree into html table
 *
 * @name LoadDataRender
 * @package OMOP
 * @subpackage WebRl
 * @source LoadDataRender.php
 */
class LoadDataRender {
    /**
     * @property Array $_loadDataTree Tree of uploaded data
     */
    private $_loadDataTree = null;
    /**
     * @property Array $_tableTemplate two-dimensional array with information about table cells
     */
    private $_tableTemplate = array();
    /**
     * @property Boolean $_tableTemplateStatus Status of generation table template
     */
    private $_tableTemplateStatus = false;
    /**
     * @property String $_tableHtmlCode html code of load data table
     */
    private $_tableHtmlCode = '';
    /**
     * @property Boolean $_tableHtmlCodeStatus Status of generation table html code
     */
    private $_tableHtmlCodeStatus = false;
    /**
     * @property String $_error Error message
     */
    private $_error = null;
    /**
     * @property Array $_header Array with table header value
    */
    private $_header = array();
    /**
     * @property Array $_styles Array with tags that contained lists of styles
     */
    private $_styles = array();
    /**
     * @method __construct()
     * @access public
     */
    public function __construct($loadDataTree = null) {
        if ($loadDataTree != null)
            $this->setLoadDataTree($loadDataTree);
    }
    /**
     *
     * Method initializes property $_loadDataTree
     * 
     * @method setLoadDataTree
     * @access public
     * 
     * @param Array $loadDataTree Tree of uploaded data
     * 
     * @return Boolean Initialize status 
     */
    public function setLoadDataTree($loadDataTree) {
        $this->error = null;
        if ((is_array($loadDataTree)) && (count($loadDataTree)>0) ) {
            $this->_loadDataTree = $loadDataTree;
            return true;
        }
        $this->_error='Tree with load data must be not null array!';
        return false;
    }
    /**
     * Method initialize property with columns titles
     *
     * @method setHeader
     * @access public
     *
     * @param Array $columnTitles Array with columns titles
     *
     * @return Boolean result of init
     */
    public function setHeader($columnTitles) {
        if (is_array($columnTitles)) {
            $this->_header = $columnTitles;
            return true;
        }
        $this->_error = "Header data format doesn't valid!";
        return false;
    }
    /**
     * Method initialize property with html tags styles
     *
     * @method setTagStyles
     * @access public
     *
     * @param String $tagname Tag name
     * @param Array $stylesArray Array with styles list 
     *
     * @return Boolean result of init
     */
    public function setTagStyles($tagname,$stylesArray) {
        if (($tagname) && ($stylesArray) && (is_array($stylesArray))) {
            if (isset($this->_styles[$tagname])) {
                foreach ($stylesArray as $styleName => $styleValue)
                    $this->_styles[$tagname][$styleName]=$styleValue;
            }
            else
                $this->_styles[$tagname] = $stylesArray;
            return true;
        }
        $this->_error = "Tag name and style list can't be null!";
        return false;
    }
    /**
     * Method generate html string with style for choisen tag
     *
     * @method generateTagStyle
     * @access private
     *
     * @param String $tagName Tag name
     *
     * @return String Tag style
     */
    private function generateTagStyle ($tagName) {
        $style = '';
        if (isset($this->_styles[$tagName])) {
            if (count($this->_styles[$tagName]) > 0) {
                $style .= ' style="';
                foreach ($this->_styles[$tagName] as $styleName => &$styleValue)
                    $style .="{$styleName}:{$styleValue};";
                $style .= '" ';
            }
        }
        return $style;
    }
    /**
     * Recursive method wich expands black-red tree into two-dimensional array
     *
     * @method generateTableTemplate
     * @access private
     * @throws Exception
     * 
     * @param type $treeNode - node of data tree
     * @param type $parentDeeps - Deeps of parent node towards root node
     * 
     * @return two-dimensional array with information about tree cells
     */
    private function generateTableTemplate($treeNode,$parentDeeps = null) {
        if (is_int($parentDeeps))
            $nodeDeeps = $parentDeeps+1;
        else
            $nodeDeeps = 0; //if $treeNode is root
        $tableMatrix = array();
        if ((is_array($treeNode)) && (count($treeNode) > 0)) {
            foreach ($treeNode as $nodeName => $nodeContent) {
                /**
                 * If node is container wich have other conteiner run recursiv
                 */
                if (is_array($nodeContent)) {
                    if (count($nodeContent)>0) {
                        $levelRow = $this->generateTableTemplate($nodeContent,$nodeDeeps);
                        $levelRow[0][$nodeDeeps] = array("value" => $nodeName, "rowspan" => count($levelRow));
                        foreach ($levelRow as $rowLine) {
                            $tableMatrix[]=$rowLine;
                        }
                    }
                }
                /**
                 * If node contained final data build row ass array wheere each element is cell of table
                 */
                else {
                    for ($i=0;$i<=$nodeDeeps;$i++)
                        $rowMatrix[$i] = 0;
                    $rowMatrix[$nodeDeeps]=array("value" => $nodeContent);
                    $tableMatrix[] = $rowMatrix;
                }
            }
        }
        else 
            throw new Exception ("All data tree brunch must have similar deeps!");
        return $tableMatrix;
    }
    /**
     * Method generate html code of table from data tree (black-red tree)
     *
     * @method generateHtmlTable
     * @access public
     *
     * @return String html code of table
     */
    public function generateHtmlTable () {
        if (($this->_loadDataTree) && (is_array($this->_loadDataTree)) && (count($this->_loadDataTree)>0) ) {
            try {
                $this->_tableTemplate = $this->generateTableTemplate($this->_loadDataTree);
                $this->_tableTemplateStatus = true;
                $this->generateTableHtmlCode();
                return $this->_tableHtmlCode;
            }
            catch (Exception $exc) {
                $this->_error=$exc->getMessage();
                return false;
            }
        }
        $this->_error='Tree with load data must be not null array!';
        return false;
    }
    /**
     * Method generate html code from two-dimensional array with information about table cells
     * 
     * @method generateTableHtmlCode
     * @access public
     * @throws Exception
     */
    private function generateTableHtmlCode () {
        if ($this->_tableTemplateStatus == true) {
            $style = $this->generateTagStyle('table');
            $table = "<table {$style}>";
            if (($this->_header) && (is_array($this->_header)) && (count($this->_header) > 0) ) {
                $style = $this->generateTagStyle('tr');
                $table.="<tr {$style}>";
                foreach ($this->_header as $columnTitle) {
                    $style = $this->generateTagStyle('th');
                    $table.="<th {$style}>".htmlspecialchars($columnTitle)."</th>";
                }
                $table.="</tr>";
            }
            foreach ($this->_tableTemplate as $row) {
                $style = $this->generateTagStyle('tr');
                $tr = "<tr {$style}>";
                foreach ($row as $field) {
                    if (is_array($field)) {
                        $style = $this->generateTagStyle('td');
                        if (isset($field["rowspan"])) {
                            $fieldValue = htmlspecialchars($field["rowspan"]);
                            $td = "<td rowspan={$fieldValue} {$style}>";
                        }
                        else
                            $td = "<td {$style}>";
                        $td .= $field["value"]."</td>";
                        $tr.= $td;
                    }
                }
                $tr .= "</tr>";
                $table .= $tr;
            }
            $table .= "</table>";
            $this->_tableHtmlCode = $table;
            $this->_tableHtmlCodeStatus = true;
        }
        else
            throw new Exception ("Table template isn't initialized");
    }
}

?>
