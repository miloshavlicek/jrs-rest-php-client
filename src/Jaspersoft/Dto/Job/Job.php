<?php
namespace Jaspersoft\Dto\Job;

/**
 * Class Job
 *
 * This class represents a scheduled job, it consists of both simple and complex attributes.
 *
 * @package Jaspersoft\Dto\Job
 */
class Job
{

    /** Job execution alert settings
     *
     * @var Alert
     */
    public $alert;

    /** File name for report job output
     * @var string
     */
    public $baseOutputFilename;

    /** Job Execution Output Destination Settings
     * @var RepositoryDestination
     */
    public $repositoryDestination;

    /** The date when the job has been created (read-only)
     * @var string
     */
    public $creationDate;

    /** The job description
     * @var string
     */
    public $description;

    /** ID of the Job (read-only)
     * @var int
     */
    public $id;

    /** The job label
     * @var string
     */
    public $label;

    /** Mail notification settings
     * @var MailNotification
     */
    public $mailNotification;

    /** Set of output formats to produce
     *
     * Supported Values:
     *   "PDF", "HTML", "XLS", "RTF", "CSV", "ODT", "TXT", "DOCX", "ODS", "XLSX", "XLS_NOPAG",
     *   "XLSX_NOPAG", "DATA_SNAPSHOT"
     *
     * Note: DATA_SNAPSHOT is only saved if data snapshot is enabled in applicationContext-data-snapshots.xml
     * otherwise this value is ignored
     *
     * Example: array("XLS", "HTML", "PDF");
     *
     * @var array
     */
    public $outputFormats;

    /** Locale for report execution output
     *
     * Example: "en"
     * @var string
     */
    public $outputLocale;

    /** Job Source Settings (contains report URI and input control parameters)
     * @var Source
     */
    public $source;

    /** Job Trigger Settings
     * @var SimpleTrigger|CalendarTrigger
     */
    public $trigger;

    /** Name and Organization of user who created job (read-only)
     *
     * Example: "jasperadmin|organization_1"
     *
     * @var string
     */
    public $username;

    /** Job object version value. Used for optimistic locking of job object
     *
     * @var int
     */
    public $version;

    /** Output Time Zone
     *
     * @var string */
    public $outputTimeZone;

	public function __construct($label = null, $trigger = null, $source = null, $baseOutputFilename = null,
                                $outputFormats = null, $repositoryDestination = null)
    {
        $this->label = $label;
        $this->trigger = $trigger;
        $this->source = $source;
        $this->baseOutputFilename = $baseOutputFilename;
        $this->outputFormats = $outputFormats;
        $this->repositoryDestination = $repositoryDestination;
	}

    /**
     * This function will combine all simple and complex attributes of a Job DTO and return an associative
     * array formatted for json_encode that results in the proper JSON format for server requests.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = array();
        foreach (get_object_vars($this) as $k => $v) {
            if (isset($v)) {
                if (is_object($v)) {
                    $result[$k] = $v->jsonSerialize();
                // OutputFormats requires a special case because of its hierarchical sublevel "outputFormat"
                } else if ($k == "outputFormats") {
                    $result[$k] = array("outputFormat" => $v);
                } else {
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }

    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

    /** This function takes a \stdClass decoded by json_decode representing a scheduled job
     * and casts it as a Job Object
     *
     * @param \stdClass $json_obj
     * @return Job
     */
    public static function createFromJSON($json_obj)
    {
        $result = new self();        

        // Handle complex and special cases
        // Then remove them from the data array as not to be reprocessed below
        if (isset($json_obj->alert)) {
            $result->alert = Alert::createFromJSON($json_obj->alert);
            unset($json_obj->alert);
        }
        if (isset($json_obj->trigger)) {
            $result->trigger = Trigger::createFromJSON($json_obj->trigger);
            unset ($json_obj->trigger);
        }
        if (isset($json_obj->source)) {
            $result->source = Source::createFromJSON($json_obj->source);
            unset ($json_obj->source);
        }
        if (isset($json_obj->outputFormats)) {
            $result->outputFormats = $json_obj->outputFormats->outputFormat;
            unset ($json_obj->outputFormats);
        }
        if (isset($json_obj->repositoryDestination)) {
            $result->repositoryDestination = RepositoryDestination::createFromJSON($json_obj->repositoryDestination);
            unset($json_obj->repositoryDestination);
        }
        if (isset($json_obj->mailNotification)) {
            $result->mailNotification = MailNotification::createFromJSON($json_obj->mailNotification);
            unset($json_obj->mailNotification);
        }

        // Handle the remaining simple attributes
        foreach ($json_obj as $k => $v) {
            $result->$k = $v;
        }

        return $result;
    }

}

?>