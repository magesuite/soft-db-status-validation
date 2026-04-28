<?php

declare(strict_types=1);

namespace MageSuite\SoftDbStatusValidation\Test\Unit\Service;

class DiffExplainerTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\SoftDbStatusValidation\Service\DiffExplainer $service;

    protected function setUp(): void
    {
        $this->service = $this->getMockBuilder(\MageSuite\SoftDbStatusValidation\Service\DiffExplainer::class)
            ->onlyMethods([])
            ->getMock();
    }

    public function testGetReasonsReturnsMessagesForAllChangesGroupedByOperation(): void
    {
        $diff = $this->getMockBuilder(\Magento\Framework\Setup\Declaration\Schema\Diff\Diff::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAll'])
            ->getMock();

        $diff->method('getAll')->willReturn([
            20 => [
                'add_column' => [$this->makeDiff(\Magento\Framework\Setup\Declaration\Schema\Dto\Column::class, 'varchar', 'sku')],
                'create_table' => [$this->makeDiff(\Magento\Framework\Setup\Declaration\Schema\Dto\Table::class, 'table', 'cms_page')],
            ],
            30 => [
                'add_column' => [$this->makeDiff(\Magento\Framework\Setup\Declaration\Schema\Dto\Column::class, 'varchar', 'name')],
            ],
            40 => [
                'create_index' => [$this->makeDiff(\Magento\Framework\Setup\Declaration\Schema\Dto\Index::class, 'index', 'NAME')],
            ],
            50 => [
                'drop_table' => [$this->makeDiff(\Magento\Framework\Setup\Declaration\Schema\Dto\Table::class, 'table', 'mail', false)],
            ],
            60 => [
                'drop_element' => [$this->makeDropDiffWithBothSides(\Magento\Framework\Setup\Declaration\Schema\Dto\Column::class, 'varchar', 'legacy_code')],
            ],
        ]);

        $this->assertSame(
            [
                "Column 'sku' doesn't exist in UNKNOWN table or it has wrong definition. It should be varchar.",
                "Column 'name' doesn't exist in UNKNOWN table or it has wrong definition. It should be varchar.",
                "Missing 'cms_page' table",
                "Missing 'NAME' index",
                "Undeclared 'mail' table",
                "Column 'legacy_code' is not declared in UNKNOWN table or it has wrong definition. It should be varchar."
            ],
            $this->service->getReasons($diff)
        );
    }

    protected function makeDiff(string $class, string $type, string $name, bool $isNew = true): \Magento\Framework\Setup\Declaration\Schema\ElementHistory
    {
        $mockParams = ['getNew' => null, 'getOld' => null];
        $mockParams[$isNew ? 'getNew' : 'getOld'] = $this->createConfiguredMock($class, ['getType' => $type, 'getName' => $name]);

        return $this->createConfiguredMock(\Magento\Framework\Setup\Declaration\Schema\ElementHistory::class, $mockParams);
    }

    protected function makeDropDiffWithBothSides(string $class, string $type, string $name): \Magento\Framework\Setup\Declaration\Schema\ElementHistory
    {
        $dto = $this->createConfiguredMock($class, ['getType' => $type, 'getName' => $name]);

        return $this->createConfiguredMock(
            \Magento\Framework\Setup\Declaration\Schema\ElementHistory::class,
            ['getNew' => $dto, 'getOld' => $dto]
        );
    }
}
