<?php
/**
 * Copyright 2018 SC-Networks GmbH All Rights Reserved
 * This software is the proprietary information of SC-Networks GmbH
 * Use is subject to license terms
 */

namespace Tests\Testhelper;

use PhpTal\Dom\Element;
use PhpTal\Php\CodeWriter;

/**
 * Class DummyDefinePhpNode
 * @package Testhelper
 */
class DummyDefinePhpNode extends Element
{
    public function __construct()
    {
        // noop
    }

    public function generateCode(CodeWriter $codewriter)
    {
        // noop
    }
}
