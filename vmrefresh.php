<?php

/**
 * vmrefresh
 *
 * Refresh count virtual mail boxes after move or delete message in real mail moxes.
 *
 * @version @package_version@
 * @license GNU GPLv3+
 * @author Sergey Sidlyarenko
 */

class vmrefresh extends rcube_plugin
{
    private $rc;

    function init()
    {
        $this->rc = rcmail::get_instance();

        $this->add_hook('move_del_success', array($this,'refresh_virtual_mailboxes'));
        $this->add_hook('show_success', array($this,'refresh_virtual_mailboxes'));
        $this->add_hook('mark_success', array($this,'refresh_virtual_mailboxes'));
    }

    function refresh_virtual_mailboxes($args)
    {
        $this->load_config();

        $a_mailboxes = $this->rc->config->get('vmrefresh_mailboxes', false);
        $current   = $this->rc->storage->get_folder();

        // check recent/unseen counts
        foreach ($a_mailboxes as $mbox_name) {

            $is_current = $mbox_name == $current;

            if ($is_current)
                continue;

            $this->rc->storage->folder_sync($mbox_name);


            // Get mailbox status
            $status = $this->rc->storage->folder_status($mbox_name, $diff);

            if (empty($_REQUEST['_framed']))
                rcmail_send_unread_count($mbox_name, true, null,
                    (!$is_current && ($status & 1)) ? 'recent' : '');
            else
                rcmail_send_unread_count($mbox_name, true, null,
                    (!$is_current && ($status & 1)) ? 'recent' : '', true);

        }

    }


}
