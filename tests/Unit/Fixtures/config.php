<?php

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file.
 * Redistributions of files must retain the above copyright notice.
 * 
 * @copyright (c) 2014, Achmad F. Ibrahim
 * @link https://github.com/Achsoft
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */

/**
 * Configuration file
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 * @package Achsoft\Component\ServiceLocator
 * @version 1.0
 * @since 1.0
 */
return [
    
    // string classname
    'first' => '\Tests\Unit\Fixtures\FirstDependency',
    
    // closure
    'second' => function ($sl) {
        return new \Tests\Unit\Fixtures\SecondDependency();
    },
    
    // closure with two dependencies
    'dependant' => function ($sl) {
        $first = $sl->resolve('first');
        $second = $sl->resolve('second');
        return new \Tests\Unit\Fixtures\Dependant($first, $second);
    }
];
