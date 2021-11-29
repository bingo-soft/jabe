<?php

namespace BpmPlatform\Engine\Task;

interface AttachmentInterface
{
    /** unique id for this attachment */
    public function getId(): string;

    /** free user defined short (max 255 chars) name for this attachment */
    public function getName(): string;

    /** free user defined short (max 255 chars) name for this attachment */
    public function setName(string $name): void;

    /** long (max 255 chars) explanation what this attachment is about in context of the task and/or process instance it's linked to. */
    public function getDescription(): ?string;

    /** long (max 255 chars) explanation what this attachment is about in context of the task and/or process instance it's linked to. */
    public function setDescription(string $description): void;

    /** indication of the type of content that this attachment refers to. Can be mime type or any other indication. */
    public function getType(): string;

    /** reference to the task to which this attachment is associated. */
    public function getTaskId(): string;

    /** reference to the process instance to which this attachment is associated. */
    public function getProcessInstanceId(): string;

    /** the remote URL in case this is remote content.  If the attachment content was
     * {@link TaskService#createAttachment(String, String, String, String, String, java.io.InputStream) uploaded with an input stream},
     * then this method returns null and the content can be fetched with {@link TaskService#getAttachmentContent(String)}. */
    public function getUrl(): string;

    /** The time when the attachment was created. */
    public function getCreateTime(): string;

    /** reference to the root process instance id of the process instance on which this attachment was made */
    public function getRootProcessInstanceId(): string;

    /** The time the historic attachment will be removed. */
    public function getRemovalTime(): string;
}
