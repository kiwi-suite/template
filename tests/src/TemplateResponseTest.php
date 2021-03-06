<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Template;

use Ixocreate\Template\TemplateResponse;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ixocreate\Template\TemplateResponse
 */
class TemplateResponseTest extends TestCase
{
    public function testTemplateResponse()
    {
        $template = 'test::test';
        $data = ['foo' => 'bar'];
        $globalData = ['globalFoo' => 'globalBar'];

        $response = new TemplateResponse(
            $template,
            $data,
            $globalData,
            200,
            ['header' => 'value']
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($data, $response->getData());
        $this->assertSame($globalData, $response->getGlobalData());
        $this->assertSame($template, $response->getTemplate());
    }
}
