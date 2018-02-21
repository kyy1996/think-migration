<?php
/**
 * Created by PhpStorm.
 * User: alen
 * Date: 12/02/2018
 * Time: 7:07 PM
 */

namespace think\migration\adapter;


use Symfony\Component\Console\Input\InputOption;
use think\console\input\Definition;
use think\console\input\Option;

class InputDefinitionAdapter extends Definition
{
    /**
     * 设置指令的定义
     * @param array $definition 定义的数组
     * @throws \ReflectionException
     */
    public function setDefinition(array $definition)
    {
        $arguments = [];
        $options   = [];
        foreach ($definition as $item) {
            if ($item instanceof InputOption) {
                $name        = $item->getName();
                $shortcut    = $item->getShortcut();
                $mode        = (new \ReflectionClass($item))->getProperty("mode")->getValue($item);
                $description = $item->getDescription();
                $default     = $item->getDefault();
                $option      = new Option($name, $shortcut, $mode, $description, $default);
                $options[]   = $option;
            } else {
                $arguments[] = $item;
            }
        }

        $this->setArguments($arguments);
        $this->setOptions($options);
    }
}