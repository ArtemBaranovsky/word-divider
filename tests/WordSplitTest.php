<?php

namespace tests;

use Classes\WordSplitter;
use PHPUnit\Framework\TestCase;

class WordSplitTest extends TestCase
{

    public function testSameSplit() : void
    {
        $this->assertSame(
            WordSplitter::wordBreak('утро трианекдотколхозанализидти'),
            'утро три анекдот колхоз анализ идти'
        );
    }
}