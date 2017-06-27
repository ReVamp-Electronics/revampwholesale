<?php
namespace Evdpl\Ourteam\Model\Post\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Evdpl\Ourteam\Model\Post
     */
    protected $post;

    /**
     * Constructor
     *
     * @param \Evdpl\Ourteam\Model\Post $post
     */
    public function __construct(\Evdpl\Ourteam\Model\Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->post->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
