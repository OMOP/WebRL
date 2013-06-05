<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Handle operations with S3.
 
    (c)2009 Foundation for the National Institutes of Health (FNIH)
 
    Licensed under the Apache License, Version 2.0 (the "License"); you may not
    use this file except in compliance with the License. You may obtain a copy
    of the License at http://omop.fnih.org/publiclicense.
 
    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
    WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. Any
    redistributions of this work or any derivative work or modification based on
    this work should be accompanied by the following source attribution: "This
    work is based on work by the Observational Medical Outcomes Partnership
    (OMOP) and used under license from the FNIH at
    http://omop.fnih.org/publiclicense.
 
    Any scientific publication that is based on this work should include a
    reference to http://omop.fnih.org.
 
================================================================================*/
require_once('s3-php5-curl/S3.php');

class S3Manager extends S3
{
    private $folder_suffix = '_$folder$';
    private $folder_separator = '/';

    /**
     * return the folder separator used on S3 instance
     * @return string folder/file separator
     */
    public function getFolderSeparator()
    {
        return $this->folder_separator;
    }

    /**
     * Checks whether the file or folder exists on bucket
     * @param string bucket name
     * @param string $path path to the file
     * @param boolean $is_folder is the searched item a folder
     * @return boolean result
     */
    public function checkFileExists($bucket, $path, $is_folder = false)
    {
        $exists = false;

        if (($contents = $this->getBucket($bucket, $path)) !== false) {
            foreach ($contents as $object) {
                if ($object['name'] === $path) {
                    /**
                     * File exists
                     */
                    $exists = true;                    
                    break;
                } elseif (
                    $is_folder &&
                    ($object['name'] === $path . $this->folder_suffix ||
                        substr($object['name'], 0, strlen($path . $this->folder_separator)) == $path . $this->folder_separator) )
                {
                    /**
                     * Folder exists
                     */
                    $exists = true;
                    break;
                }
            }
        }

        return $exists;
    }

    /**
     * Removes S3 folder with its content
     * @param string bucket name
     * @param string path to the directory to remove
     */
    public function deleteFolder($bucket, $path)
    {
        $result = true;

        $folder_suffix_length = strlen($this->folder_suffix);

        $folder_separator_length = strlen($this->folder_separator);

        if (($contents = $this->getBucket($bucket, $path)) !== false) {
            $is_folder = false;
            foreach ($contents as $name => $object) {
                if ($name !== $path && // file itself
                    $name !== $path . $folder_suffix && // folder itself
                    substr($name, 0, strlen($path) + $folder_separator_length) !== $path . $this->folder_separator) // child object
                {
                    unset ($contents[$name]);
                    continue;
                }

                if ($name === $path) {
                    /**
                     * This is a file. Just delete it.
                     */
                    $result = $this->deleteObject($bucket, $name);
                }
                elseif (substr($name, 0-$folder_suffix_length) !== $this->folder_suffix) {
                    /**
                     * Just a file inside dir.
                     */
                    $result = $this->deleteObject($bucket, $name);
                }
                elseif ($name === $path.$this->folder_suffix) {
                    /**
                     * Folder itself. Will delete later
                     */
                    $is_folder = true;
                }
                else {
                    /**
                     * @todo rework it not to use recursion
                     */
                    $this->removeS3Object(substr($name, 0, strlen(name)-$folder_suffix_length));
                }
            }
            if ($is_folder) {
                $result = $this->deleteObject($bucket, $path . $folder_suffix);
            }
        }

        return $result;
    }
}

?>
