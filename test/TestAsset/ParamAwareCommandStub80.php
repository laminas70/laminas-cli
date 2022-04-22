<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

class ParamAwareCommandStub80 extends AbstractParamAwareCommandStub
{
    /**
     * @param string|array|null $shortcut
     * @param null|mixed        $default Defaults to null.
     * @return $this
     * @param string $name
     * @param int|null $mode
     * @param string $description
     */
    public function addOption(
        $name,
        $shortcut = null,
        $mode = null,
        $description = '',
        $default = null
    ) {
        return $this->doAddOption($name, $shortcut, $mode, $description, $default);
    }
}
