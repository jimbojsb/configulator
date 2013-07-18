<?php
namespace Configulator;

class ConfigFile
{
    public static function getOptions($file, $profile = null, $localFile = null)
    {
        if (file_exists($file)) {
            $f = new \SplFileObject($file);
            switch ($f->getExtension()) {
                case "php":
                    $standardOptions = include $file;
                    $localOptions = include $localFile;
                    break;
                case "yaml":
                case "yml":
                    $parser = new \Symfony\Component\Yaml\Parser();
                    $standardOptions = $parser->parse(file_get_contents($file));
                    $localOptions = $parser->parse(file_get_contents($localFile));
                    break;
                case "json":
                    $standardOptions = json_decode(file_get_contents($file), true);
                    $localOptions = json_decode(file_get_contents($localFile), true);
                    break;
                default:
                    throw new \InvalidArgumentException("File type " . $f->getExtension() . " not supported");
            }

            if ($localFile) {
                $options = array_replace_recursive($standardOptions, $localOptions);
            } else {
                $options = $standardOptions;
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
                    $inherit = array($inherit);
                }
                $resolvedOptions = array();
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