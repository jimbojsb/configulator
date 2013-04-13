<?php
namespace Configulator;

class ConfigFile
{
    public static function getOptions($file, $profile = null)
    {
        if (file_exists($file)) {
            $f = new \SplFileObject($file);
            switch ($f->getExtension()) {
                case "php":
                    $options = include $file;
                    break;
                case "yaml":
                case "yml":
                    $parser = new \Symfony\Component\Yaml\Parser();
                    $options = $parser->parse(file_get_contents($file));
                    break;
                case "json":
                    $options = json_decode(file_get_contents($file), true);
                    break;
                default:
                    throw new \InvalidArgumentException("File type " . $f->getExtension() . " not supported");
            }

            if (!is_array($options)) {
                throw new \RuntimeException("Found no usable data in $file");
            } else {
                if ($profile) {
                    return self::resolveInheritance($options, $profile);
                }
                return $options;
            }

        } else {
            throw new \InvalidArgumentException("$file does not exist or could not be read");
        }

    }

    public static function resolveInheritance(array $options, $profile)
    {
        if ($profile && isset($options[$profile])) {
            $profileOptions = $options[$profile];
            $inherit = $profileOptions['inherit'];
            if ($inherit) {
                unset($profileOptions['inherit']);
                if (!is_array($inherit)) {
                    $inherit = [$inherit];
                }
                $resolvedOptions = [];
                foreach ($inherit as $inheritedProfile) {
                    $resolvedOptions = array_replace_recursive($resolvedOptions, $options[$inheritedProfile]);
                }
                $resolvedOptions = array_replace_recursive($resolvedOptions, $profileOptions);
                return $resolvedOptions;
            } else {
                return $profileOptions;
            }
        }
        return $options;
    }
}