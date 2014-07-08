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
 * Test configuration file
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 */
return [
    
    // string classname
    'first' => '\Test\Fixture\FirstDependency',
    
    // closure
    'second' => function ($sl) {
        return new \Test\Fixture\SecondDependency();
    },
    
    // closure with two dependencies
    'dependant' => function ($sl) {
        $first = $sl->get('first');
        $second = $sl->get('second');
        return new \Test\Fixture\Dependant($first, $second);
    }
];
