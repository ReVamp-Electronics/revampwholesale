<?php
namespace Evdpl\Ourteam\Api\Data;


interface PostInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const POST_ID       = 'post_id';
    const URL_KEY       = 'designation';
    const TITLE         = 'title';
    const CONTENT       = 'content';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME   = 'update_time';
    const IS_ACTIVE     = 'is_active';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Designation
     *
     * @return string
     */
    public function getUrlKey();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Evdpl\Ourteam\Api\Data\PostInterface
     */
    public function setId($id);

    /**
     * Set Designation
     *
     * @param string $designation
     * @return \Evdpl\Ourteam\Api\Data\PostInterface
     */
    public function setUrlKey($designation);

    /**
     * Return full URL including base url.
     *
     * @return mixed
     */
    public function getUrl();

    /**
     * Set title
     *
     * @param string $title
     * @return \Evdpl\Ourteam\Api\Data\PostInterface
     */
    public function setTitle($title);

    /**
     * Set content
     *
     * @param string $content
     * @return \Evdpl\Ourteam\Api\Data\PostInterface
     */
    public function setContent($content);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Evdpl\Ourteam\Api\Data\PostInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Evdpl\Ourteam\Api\Data\PostInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Evdpl\Ourteam\Api\Data\PostInterface
     */
    public function setIsActive($isActive);
}
