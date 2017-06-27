<?php
namespace Aheadworks\SocialLogin\Model\Config;

/**
 * Class LoginBlock
 */
class LoginBlock extends AbstractConfig
{
    const XML_PATH_DEFAULT_VISIBILITY = 'social/login_block/default_is_visible';
    const XML_PATH_DEFAULT_TEMPLATE = 'social/login_block/default_template';
    const XML_PATH_DEFAULT_GROUPS = 'social/login_block/default_groups';
    const XML_PATH_GROUPS_SETTINGS = 'social/login_block/group_setting';

    /**
     * Is visible default
     *
     * @return bool
     */
    public function isVisibleDefault()
    {
        return $this->isSetFlag(self::XML_PATH_DEFAULT_VISIBILITY);
    }

    /**
     * Get default template
     *
     * @return string
     */
    public function getDefaultTemplate()
    {
        return $this->getValue(self::XML_PATH_DEFAULT_TEMPLATE);
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getGroups()
    {
        return array_keys($this->getGroupsSettings());
    }

    /**
     * Get default groups settings
     *
     * @return array
     */
    public function getDefaultGroupsSettings()
    {
        $groups = $this->getDefaultGroups();
        $groupsSettings = [];
        foreach ($groups as $group) {
            $groupsSettings[$group] = [
                'group' => $group,
                'is_visible' => (int)$this->isVisibleDefault(),
                'template' => $this->getDefaultTemplate()
            ];
        }
        return $groupsSettings;
    }

    /**
     * Get groups settings
     *
     * @param array|null $customSettings
     * @return array
     */
    public function getGroupsSettings($customSettings = null)
    {
        $customSettings = is_array($customSettings) ? $customSettings : $this->getCustomGroupsSettings();
        $defaultSettings = $this->getDefaultGroupsSettings();

        $settings = $defaultSettings;
        foreach ($customSettings as $group => $setting) {
            if (isset($settings[$group])) {
                $customSetting = array_intersect_key($setting, array_flip(['template', 'is_visible']));
                $settings[$group] = array_replace($settings[$group], $customSetting);
            } else {
                $settings[$group] = $setting;
            }
        }
        return $settings;
    }

    /**
     * Get group settings
     *
     * @param string $groupName
     * @return array|null
     */
    public function getGroupSettings($groupName)
    {
        $groupsSettings = $this->getGroupsSettings();
        return isset($groupsSettings[$groupName]) ? $groupsSettings[$groupName] : null;
    }

    /**
     * Get custom groups settings
     *
     * @return array
     */
    protected function getCustomGroupsSettings()
    {
        $settings = $this->getSerializedValue(self::XML_PATH_GROUPS_SETTINGS);
        $settings = is_array($settings) ? $settings : [];
        return $this->prepareCustomGroupsSettings($settings);
    }

    /**
     * Prepare custom groups settings
     *
     * @param array $settings
     * @return array
     */
    public function prepareCustomGroupsSettings(array $settings)
    {
        $groupsSettings = [];
        foreach ($settings as $id => $setting) {
            $setting['_id'] = $id;
            if (isset($setting['group'])) {
                $groupsSettings[$setting['group']] = $setting;
            }
        }

        return $groupsSettings;
    }

    /**
     * Get default groups
     *
     * @return array
     */
    public function getDefaultGroups()
    {
        return (array)$this->getValue(self::XML_PATH_DEFAULT_GROUPS);
    }
}
