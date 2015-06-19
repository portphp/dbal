<?php

namespace spec\Port\Dbal;

use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class DbalReaderFactorySpec extends ObjectBehavior
{
    function let(Connection $dbal)
    {
        $this->beConstructedWith($dbal);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Port\Dbal\DbalReaderFactory');
    }

    function it_creates_a_reader()
    {
        $this->getReader('SQL', [])->shouldHaveType('Port\Dbal\DbalReader');
    }
}
