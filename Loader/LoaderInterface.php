<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paulo
 * Date: 3/28/12
 * Time: 5:57 AM
 * To change this template use File | Settings | File Templates.
 */
namespace BFOS\SyncContentBundle\Loader;

interface LoaderInterface
{
    /**
     * Loads the filename
     *
     * @return array
     */
    function load($filename);
}