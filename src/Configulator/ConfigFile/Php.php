<?php
namespace Configulator\ConfigFile;

class Php
{
    public static function getOptions($file, $profile = null)
    {
        if (file_exists($file)) {
            $options = include $file;
            if (!is_array($options)) {
                throw new \RuntimeException("Found no data in $file, did you return an array?");
            } else {
                if ($profile && isset($profile[$profile])) {
                    $profileOptions = $options[$profile];
                    $inherit = $profileOptions['inherit'];
                    if ($inherit) {
                        unset($profile['inherit']);
                        if (!is_array($inherit)) {
                            $inherit = [$inherit];
                        }
                        $resolvedOptions = [];
                        foreach ($inherit as $inheritedProfile) {
                            $resolvedOptions = array_merge_recursive($options[$inheritedProfile], $resolvedOptions);
                        }
                        $resolvedOptions = array_merge_recursive($resolvedOptions, $profileOptions);
                        return $resolvedOptions;
                    } else {
                        return $profileOptions;
                    }
                }
                return $options;
            }
        } else {
            throw new \InvalidArgumentException("$file does not exist or could not be read");
        }
    }
}