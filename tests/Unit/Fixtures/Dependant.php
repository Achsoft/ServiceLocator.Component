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

namespace Tests\Unit\Fixtures;

use Tests\Unit\Fixtures\FirstDependency;
use Tests\Unit\Fixtures\SecondDependency;

/**
 * Fixture class that depends another classes.
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 */
class Dependant
{
    public function __construct(FirstDependency $first, SecondDependency $second)
    {
        $this->first = $first;
        $this->second = $second;
    }
}
