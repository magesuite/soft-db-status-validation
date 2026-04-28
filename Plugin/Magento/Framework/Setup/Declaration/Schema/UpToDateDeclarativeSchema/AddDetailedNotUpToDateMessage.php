<?php

declare(strict_types=1);

namespace MageSuite\SoftDbStatusValidation\Plugin\Magento\Framework\Setup\Declaration\Schema\UpToDateDeclarativeSchema;

class AddDetailedNotUpToDateMessage
{
    public function __construct(
        protected \Magento\Framework\Setup\Declaration\Schema\SchemaConfigInterface $schemaConfig,
        protected \Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff $schemaDiff,
        protected \MageSuite\SoftDbStatusValidation\Service\DiffExplainer $diffExplainer,
    ) {}

    public function afterGetNotUpToDateMessage(
        \Magento\Framework\Setup\Declaration\Schema\UpToDateDeclarativeSchema $subject,
        string $result
    ): string {
        $reasons = $this->diffExplainer->getReasons($this->getDiff());

        return "Declarative Schema is not up to date because:\n - " . implode("\n - ", $reasons);
    }

    protected function getDiff(): \Magento\Framework\Setup\Declaration\Schema\Diff\Diff
    {
        $declarativeSchema = $this->schemaConfig->getDeclarationConfig();
        $databaseSchema = $this->schemaConfig->getDbConfig();

        return $this->schemaDiff->diff($declarativeSchema, $databaseSchema);
    }
}

