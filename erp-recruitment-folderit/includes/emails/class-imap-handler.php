<?php

/**
 * The Imap PHP class provides a wrapper for commonly used PHP IMAP functions.
 *
 * This class was originally written by Josh Grochowski, and was reformatted and
 * documented by Jeff Geerling.
 *
 * Usage examples can be found in the included README file, and all methods
 * should have adequate documentation to get you started.
 *
 * Quick Start:
 * @code
 *   include 'path/to/Imap/JJG/Imap.php';
 *   use \JJG\Imap as Imap;
 *   $mailbox = new Imap($host, $user, $pass, $port, $ssl, $folder);
 *   $mailbox->getMailboxInfo();
 * @endcode
 *
 * Minimum requirements: PHP 5.3.x, php5-imap
 *
 * @version 1.0-beta2
 * @author Josh Grochowski (josh[at]kastang[dot]com).
 * @author Jeff Geerling (geerlingguy).
 */

namespace JJG;

class Imap {

  private $host;
  private $user;
  private $pass;
  private $port;
  private $folder;
  private $validateCert;
  private $readOnly;
  private $ssl;

  private $baseAddress;
  private $address;
  private $mailbox;

  /**
   * Called when the Imap object is created.
   *
   * Sample of a complete address: {imap.gmail.com:993/imap/ssl}INBOX
   *
   * @param $host (string)
   *   The IMAP hostname. Example: imap.gmail.com
   * @param $port (int)
   *   Example: 933
   * @param $ssl (bool)
   *   TRUE to use SSL, FALSE for no SSL.
   * @param $folder (string)
   *   IMAP Folder to open.
   * @param $user (string)
   *   Username used for connection. Gmail uses full username@gmail.com, but
   *   many providers simply use username.
   * @param $pass (string)
   *   Account password.
   *
   * @return (empty)
   */
  public function __construct($host, $user, $pass, $port, $ssl = true, $folder = 'INBOX', $validateCert = true, $readOnly = false) {
    if ((!isset($host)) || (!isset($user)) || (!isset($pass)) || (!isset($port))) {
      throw new \Exception("Error: All Constructor values require a non NULL input.");
    }

    $this->host = $host;
    $this->user = $user;
    $this->pass = $pass;
    $this->port = $port;
    $this->folder = $folder;
    $this->validateCert = $validateCert;
    $this->readOnly = $readOnly;
    $this->ssl = $ssl;

    $this->changeLoginInfo($host, $user, $pass, $port, $ssl, $folder, $validateCert, $readOnly);
  }

  /**
   * Change IMAP folders and reconnect to the server.
   *
   * @param $folderName
   *   The name of the folder to change to.
   *
   * @return (empty)
   */
  public function changeFolder($folderName) {
    if ($this->ssl) {
      $address = '{' . $this->host . ':' . $this->port . '/imap/ssl';
    } else {
      $address = '{' . $this->host . ':' . $this->port . '/imap';
    }

    if(!$this->validateCert) {
      $address .= '/novalidate-cert';
    }

    if($this->readOnly) {
      $address .= '/readonly';
    }

    $address .= '}' . $folderName;

    $this->address = $address;
    $this->reconnect();
  }

  /**
   * Log into an IMAP server.
   *
   * This method is called on the initialization of the class (see
   * __construct()), and whenever you need to log into a different account.
   *
   * Please see __construct() for parameter info.
   *
   * @return (empty)
   *
   * @throws Exception when IMAP can't connect.
   */
  public function changeLoginInfo($host, $user, $pass, $port, $ssl, $folder, $validateCert, $readOnly) {
    if ($ssl) {
      $baseAddress = '{' . $host . ':' . $port . '/imap/ssl';
    } else {
      $baseAddress = '{' . $host . ':' . $port . '/imap';
    }

    if(!$validateCert) {
      $baseAddress .= '/novalidate-cert';
    }

    if($readOnly) {
      $baseAddress .= '/readonly';
    }

    $address = $baseAddress . '}' . $folder;

    // Set the new address and the base address.
    $this->baseAddress = $baseAddress;
    $this->address = $address;

    // Open new IMAP connection
    if ($mailbox = imap_open($address, $user, $pass)) {
      $this->mailbox = $mailbox;
    } else {
      throw new \Exception("Error: " . imap_last_error());
    }
  }

  /**
   * Returns an associative array with detailed information about a given
   * message.
   *
   * @param $messageId (int)
   *   Message id.
   *
   * @return Associative array with keys (strings unless otherwise noted):
   *   raw_header
   *   to
   *   from
   *   cc
   *   bcc
   *   reply_to
   *   sender
   *   date_sent
   *   subject
   *   deleted (bool)
   *   answered (bool)
   *   draft (bool)
   *   body
   *   original_encoding
   *   size (int)
   *   auto_response (bool)
   *
   * @throws Exception when message with given id can't be found.
   */
  public function getMessage($messageId) {
    $this->tickle();
    $message = array();

    // Get message details.
    $details = imap_headerinfo($this->mailbox, $messageId);
    if ($details) {
      $uid = imap_uid($this->mailbox, $messageId);
      // Get the raw headers.
      $raw_header = imap_fetchheader($this->mailbox, $messageId);

      // Detect whether the message is an autoresponse.
      $autoresponse = $this->detectAutoresponder($raw_header);

      // Get some basic variables.
      $deleted = ($details->Deleted == 'D');
      $answered = ($details->Answered == 'A');
      $draft = ($details->Draft == 'X');

      // Get the message body.
      $body = imap_fetchbody($this->mailbox, $messageId, 1.2);
      if (!strlen($body) > 0) {
        $body = imap_fetchbody($this->mailbox, $messageId, 1);
      }

      // Get the message body encoding.
      $encoding = $this->getEncodingType($messageId);

      // Decode body into plaintext (8bit, 7bit, and binary are exempt).
      if ($encoding == 'BASE64') {
        $body = $this->decodeBase64($body);
      }
      elseif ($encoding == 'QUOTED-PRINTABLE') {
        $body = $this->decodeQuotedPrintable($body);
      }
      elseif ($encoding == '8BIT') {
        $body = $this->decode8Bit($body);
      }
      elseif ($encoding == '7BIT') {
        $body = $this->decode7Bit($body);
      }

      $receivers = '';
      $receiversadresses = '';
      if(!empty($details->to)) {
        foreach($details->to as $receiver) {
          if(!empty($receiversadresses)) {
            $receivers .= ', ';
            $receiversadresses .= ', ';
          }
          if(!empty($receiver->personal)) {
            $receivers .= $receiver->personal;
          }
          $receiversadresses .= $receiver->mailbox . '@' . $receiver->host;
        }
      }

      $senders = '';
      $sendersadresses = '';
      if(!empty($details->from)) {
        foreach($details->from as $sender) {
          if(!empty($sendersadresses)) {
            $senders .= ', ';
            $sendersadresses .= ', ';
          }
          if(!empty($sender->personal)) {
            $senders .= $sender->personal;
          }
          $sendersadresses .= $sender->mailbox . '@' . $sender->host;
        }
      }

      $ccs = '';
      $ccsadresses = '';
      if(!empty($details->cc)) {
        foreach($details->cc as $cc) {
          if(!empty($ccsadresses)) {
            $ccs .= ', ';
            $ccsadresses .= ', ';
          }
          if(!empty($cc->personal)) {
            $ccs .= $cc->personal;
          }
          $ccsadresses .= $cc->mailbox . '@' . $cc->host;
        }
      }

      // Build the message.
      $message = array(
        'raw_header' => $raw_header,

        'to' => $receiversadresses,
        'from' => $sendersadresses,
        'cc' => isset($ccsadresses) ? $ccsaddresses : '',

        'to_raw' => isset($details->toaddress) ? $details->toaddress : '',
        'from_raw' => isset($details->fromaddress) ? $details->fromaddress : '',
        'cc_raw' => isset($details->ccaddress) ? $details->ccaddress : '',
        
        'to_array' => isset($details->to) ? $details->to : array(),
        'from_array' => isset($details->from) ? $details->from : array(),
        'cc_array' => isset($details->cc) ? $details->cc : array(),

        'bcc' => isset($details->bccaddress) ? $details->bccaddress : '',
        'reply_to' => isset($details->reply_toaddress) ? $details->reply_toaddress : '',

        'sender' => $senders,
        'receiver' => $receivers,
        'cc_name' => $ccs,

        'date_sent' => $details->date,
        'subject' => $details->subject,
        'deleted' => $deleted,
        'answered' => $answered,
        'draft' => $draft,
        'body' => $body,
        'original_encoding' => $encoding,
        'size' => $details->Size,
        'auto_response' => $autoresponse,
        'uid' => $uid,
      );
    }
    else {
      throw new \Exception("Message could not be found: " . imap_last_error());
    }

    return $message;
  }

  public function searchMessages($criteria = '') {
    return imap_search($this->mailbox, $criteria);
  }

  /**
   * Deletes an email matching the specified $messageId.
   *
   * @param $messageId (int)
   *   Message id.
   * @param $immediate (bool)
   *   Set TRUE if message should be deleted immediately. Otherwise, message
   *   will not be deleted until disconnect() is called. Normally, this is a
   *   bad idea, as other message ids will change if a message is deleted.
   *
   * @return (empty)
   *
   * @throws Exception when message can't be deleted.
   */
  public function deleteMessage($messageId, $immediate = FALSE) {
    $this->tickle();

    // Mark message for deletion.
    if (!imap_delete($this->mailbox, $messageId)) {
      throw new \Exception("Message could not be deleted: " . imap_last_error());
    }

    // Immediately delete the message if $immediate is TRUE.
    if ($immediate) {
      imap_expunge($this->mailbox);
    }
  }

  /**
   * Moves an email into the given mailbox.
   *
   * @param $messageId (int)
   *   Message id.
   * @param $folder (string)
   *   The name of the folder (mailbox) into which messages should be moved.
   *   $folder could either be the folder name or 'INBOX.foldername'.
   *
   * @return (bool)
   *   Returns TRUE on success, FALSE on failure.
   */
  public function moveMessage($messageId, $folder) {
    $messageRange = $messageId . ':' . $messageId;
    return imap_mail_move($this->mailbox, $messageRange, '{sslmail.webguyz.net:143/imap}Questionable');
  }

  /**
   * Returns an associative array with email subjects and message ids for all
   * messages in the active $folder.
   *
   * @return Associative array with message id as key and subject as value.
   */
  public function getMessageIds() {
    $this->tickle();

    // Fetch overview of mailbox.
    $number_messages = imap_num_msg($this->mailbox);
    if ($number_messages) {
      $overviews = imap_fetch_overview($this->mailbox, "1:" . imap_num_msg($this->mailbox), 0);
    }
    else {
      $overviews = array();
    }
    $messageArray = array();

    // Loop through message overviews, build message array.
    foreach($overviews as $overview) {
      $messageArray[$overview->msgno] = $overview->subject;
    }

    return $messageArray;
  }

  /**
   * Return an associative array containing the number of recent, unread, and
   * total messages.
   *
   * @return Associative array with keys:
   *   unread
   *   recent
   *   total
   */
  public function getCurrentMailboxInfo() {
    $this->tickle();

    // Get general mailbox information.
    $info = imap_status($this->mailbox, $this->address, SA_ALL);
    $mailInfo = array(
      'unread' => $info->unseen,
      'recent' => $info->recent,
      'total' => $info->messages,
    );
    return $mailInfo;
  }

  /**
   * Return an array of objects containing mailbox information.
   *
   * @return Array of mailbox names.
   */
  public function getMailboxInfo() {
    $this->tickle();

    // Get all mailbox information.
    $mailboxInfo = imap_getmailboxes($this->mailbox, $this->baseAddress, '*');
    $mailboxes = array();
    foreach ($mailboxInfo as $mailbox) {
      // Remove baseAddress from mailbox name.
      $mailboxes[] = array(
        'mailbox' => $mailbox->name,
        'name' => str_replace($this->baseAddress, '', $mailbox->name),
      );
    }

    return $mailboxes;
  }

  /**
   * Decodes Base64-encoded text.
   *
   * @param $text (string)
   *   Base64 encoded text to convert.
   *
   * @return (string)
   *   Decoded text.
   */
  public function decodeBase64($text) {
    $this->tickle();
    return imap_base64($text);
  }

  /**
   * Decodes quoted-printable text.
   *
   * @param $text (string)
   *   Quoted printable text to convert.
   *
   * @return (string)
   *   Decoded text.
   */
  public function decodeQuotedPrintable($text) {
    return quoted_printable_decode($text);
  }

  /**
   * Decodes 8-Bit text.
   *
   * @param $text (string)
   *   8-Bit text to convert.
   *
   * @return (string)
   *   Decoded text.
   */
  public function decode8Bit($text) {
    return quoted_printable_decode(imap_8bit($text));
  }

  /**
   * Decodes 7-Bit text.
   *
   * PHP seems to think that most emails are 7BIT-encoded, therefore this
   * decoding method assumes that text passed through may actually be base64-
   * encoded, quoted-printable encoded, or just plain text. Instead of passing
   * the email directly through a particular decoding function, this method
   * runs through a bunch of common encoding schemes to try to decode everything
   * and simply end up with something *resembling* plain text.
   *
   * Results are not guaranteed, but it's pretty good at what it does.
   *
   * @param $text (string)
   *   7-Bit text to convert.
   *
   * @return (string)
   *   Decoded text.
   */
  public function decode7Bit($text) {
    // If there are no spaces on the first line, assume that the body is
    // actually base64-encoded, and decode it.
    $lines = explode("\r\n", $text);
    $first_line_words = explode(' ', $lines[0]);
    if ($first_line_words[0] == $lines[0] || $this->is_base64($text)) {
      $text_dummy = base64_decode($text, true);
      
      if(false !== $text_dummy) {
        $text = $text_dummy;
      }
    }

    // Manually convert common encoded characters into their UTF-8 equivalents.
    $characters = array(
      '=E2=80=99' => "'", // single quote.
      '=E2=80=A6' => '&hellip;', // ellipsis.
      '=E2=80=A2' => '&bull;', // bullet.
      '=E2=80=93' => '&ndash;', // en dash.
      '=E2=80=94' => '&mdash;', // em dash.
      '=E2=80=9C' => '“',
      '=E2=80=9D' => '”',
      '=E2=80=9A' => ',',
      '=E2=80=A8' => ' ',

      '=C2=A1' => '¡',
      '=C2=A2' => '¢',
      '=C2=A3' => '£',
      '=C2=A4' => '¤',
      '=C2=A5' => '¥',
      '=C2=A6' => '¦',
      '=C2=A7' => '§',
      '=C2=A8' => '¨',
      '=C2=A9' => '©',
      '=C2=AA' => 'ª',
      '=C2=AB' => '«',
      '=C2=AC' => '¬',
      '=C2=AD' => '',
      '=C2=AE' => '®',
      '=C2=AF' => '¯',
      '=C2=B0' => '°',
      '=C2=B1' => '±',
      '=C2=B2' => '²',
      '=C2=B3' => '³',
      '=C2=B4' => '´',
      '=C2=B5' => 'µ',
      '=C2=B6' => '¶',
      '=C2=B7' => '·',
      '=C2=B8' => '¸',
      '=C2=B9' => '¹',
      '=C2=BA' => 'º',
      '=C2=BB' => '»',
      '=C2=BC' => '¼',
      '=C2=BD' => '½',
      '=C2=BE' => '¾',
      '=C2=BF' => '¿',
      '=C3=80' => 'À',
      '=C3=81' => 'Á',
      '=C3=82' => 'Â',
      '=C3=83' => 'Ã',
      '=C3=84' => 'Ä',
      '=C3=85' => 'Å',
      '=C3=86' => 'Æ',
      '=C3=87' => 'Ç',
      '=C3=88' => 'È',
      '=C3=89' => 'É',
      '=C3=8A' => 'Ê',
      '=C3=8B' => 'Ë',
      '=C3=8C' => 'Ì',
      '=C3=8D' => 'Í',
      '=C3=8E' => 'Î',
      '=C3=8F' => 'Ï',
      '=C3=90' => 'Ð',
      '=C3=91' => 'Ñ',
      '=C3=92' => 'Ò',
      '=C3=93' => 'Ó',
      '=C3=94' => 'Ô',
      '=C3=95' => 'Õ',
      '=C3=96' => 'Ö',
      '=C3=97' => '×',
      '=C3=98' => 'Ø',
      '=C3=99' => 'Ù',
      '=C3=9A' => 'Ú',
      '=C3=9B' => 'Û',
      '=C3=9C' => 'Ü',
      '=C3=9D' => 'Ý',
      '=C3=9E' => 'Þ',
      '=C3=9F' => 'ß',
      '=C3=A0' => 'à',
      '=C3=A1' => 'á',
      '=C3=A2' => 'â',
      '=C3=A3' => 'ã',
      '=C3=A4' => 'ä',
      '=C3=A5' => 'å',
      '=C3=A6' => 'æ',
      '=C3=A7' => 'ç',
      '=C3=A8' => 'è',
      '=C3=A9' => 'é',
      '=C3=AA' => 'ê',
      '=C3=AB' => 'ë',
      '=C3=AC' => 'ì',
      '=C3=AD' => 'í',
      '=C3=AE' => 'î',
      '=C3=AF' => 'ï',
      '=C3=B0' => 'ð',
      '=C3=B1' => 'ñ',
      '=C3=B2' => 'ò',
      '=C3=B3' => 'ó',
      '=C3=B4' => 'ô',
      '=C3=B5' => 'õ',
      '=C3=B6' => 'ö',
      '=C3=B7' => '÷',
      '=C3=B8' => 'ø',
      '=C3=B9' => 'ù',
      '=C3=BA' => 'ú',
      '=C3=BB' => 'û',
      '=C3=BC' => 'ü',
      '=C3=BD' => 'ý',
      '=C3=BE' => 'þ',
      '=C3=BF' => 'ÿ',
      '=C2=A0' => ' ', // non-breaking space.
      '=C3=AD' => 'í',

      '=E1' => 'á',
      '=E9' => 'é',
      '=ED' => 'í',
      '=F3' => 'ó',
      '=FA' => 'ú',
      '=F1' => 'ñ',
      '=A9' => '©',
      '=0A' => "\r\n", // line break.
      '=0D' => "\r\n", // carriage return.
      '=09' => "\r\n",
      '=A0' => ' ', // non-breaking space.
      '=B9' => '$sup1', // 1 superscript.
      '=20' => ' ', // space.
      '=2C' => ',', // comma.

      "=\r\n" => '', // joined line.
    );

    // Loop through the encoded characters and replace any that are found.
    foreach ($characters as $key => $value) {
      $text = str_replace($key, $value, $text);
    }

    return $text;
  }

  /**
   * Strips quotes (older messages) from a message body.
   *
   * This function removes any lines that begin with a quote character (>).
   * Note that quotes in reply bodies will also be removed by this function,
   * so only use this function if you're okay with this behavior.
   *
   * @param $message (string)
   *   The message to be cleaned.
   * @param $plain_text_output (bool)
   *   Set to TRUE to also run the text through strip_tags() (helpful for
   *   cleaning up HTML emails).
   *
   * @return (string)
   *   Same as message passed in, but with all quoted text removed.
   *
   * @see http://stackoverflow.com/a/12611562/100134
   */
  public function cleanReplyEmail($message, $plain_text_output = FALSE) {
    // Strip markup if $plain_text_output is set.
    if ($plain_text_output) {
      $message = strip_tags($message);
    }

    // Remove quoted lines (lines that begin with '>').
    $message = preg_replace("/(^\w.+:\n)?(^>.*(\n|$))+/mi", '', $message);

    // Remove lines beginning with 'On' and ending with 'wrote:' (matches
    // Mac OS X Mail, Gmail).
    $message = preg_replace("/^(On).*(wrote:).*$/sm", '', $message);

    // Remove lines like '----- Original Message -----' (some other clients).
    // Also remove lines like '--- On ... wrote:' (some other clients).
    $message = preg_replace("/^---.*$/mi", '', $message);

    // Remove lines like '____________' (some other clients).
    $message = preg_replace("/^____________.*$/mi", '', $message);

    // Remove blocks of text with formats like:
    //   - 'From: Sent: To: Subject:'
    //   - 'From: To: Sent: Subject:'
    //   - 'From: Date: To: Reply-to: Subject:'
    $message = preg_replace("/From:.*^(To:).*^(Subject:).*/sm", '', $message);

    // Remove any remaining whitespace.
    $message = trim($message);

    return $message;
  }

  /**
   * Takes in a string of email addresses and returns an array of addresses
   * as objects. For example, passing in 'John Doe <johndoe@sample.com>'
   * returns the following array:
   *
   *     Array (   
   *       [0] => stdClass Object (
   *         [mailbox] => johndoe
   *         [host] => sample.com
   *         [personal] => John Doe
   *       )
   *     )
   *
   * You can pass in a string with as many addresses as you'd like, and each
   * address will be parsed into a new object in the returned array.
   *
   * @param $addresses (string)
   *   String of one or more email addresses to be parsed.
   *
   * @return (array)
   *   Array of parsed email addresses, as objects.
   *
   * @see imap_rfc822_parse_adrlist().
   */
  public function parseAddresses($addresses) {
    return imap_rfc822_parse_adrlist($addresses, '#');
  }

  /**
   * Create an email address to RFC822 specifications.
   *
   * @param $username (string)
   *   Name before the @ sign in an email address (example: 'johndoe').
   * @param $host (string)
   *   Address after the @ sign in an email address (example: 'sample.com').
   * @param $name (string)
   *   Name of the entity (example: 'John Doe').
   *
   * @return (string) Email Address in the following format:
   *  'John Doe <johndoe@sample.com>'
   */
  public function createAddress($username, $host, $name) {
    return imap_rfc822_write_address($username, $host, $name);
  }

  /**
   * Returns structured information for a given message id.
   *
   * @param $messageId
   *   Message id for which structure will be returned.
   *
   * @return (object)
   *   See imap_fetchstructure() return values for details.
   *
   * @see imap_fetchstructure().
   */
  public function getStructure($messageId) {
    return imap_fetchstructure($this->mailbox, $messageId);
  }

  /**
   * Returns the primary body type for a given message id.
   *
   * @param $messageId (int)
   *   Message id.
   * @param $numeric (bool)
   *   Set to true for a numerical body type.
   *
   * @return (mixed)
   *   Integer value of body type if numeric, string if not numeric.
   */
  public function getBodyType($messageId, $numeric = false) {
    // See imap_fetchstructure() documentation for explanation.
    $types = array(
      0 => 'Text',
      1 => 'Multipart',
      2 => 'Message',
      3 => 'Application',
      4 => 'Audio',
      5 => 'Image',
      6 => 'Video',
      7 => 'Other',
    );

    // Get the structure of the message.
    $structure = $this->getStructure($messageId);

    // Return a number or a string, depending on the $numeric value.
    if ($numeric) {
      return $structure->type;
    } else {
      return $types[$structure->type];
    }
  }

  /**
   * Returns the encoding type of a given $messageId.
   *
   * @param $messageId (int)
   *   Message id.
   * @param $numeric (bool)
   *   Set to true for a numerical encoding type.
   *
   * @return (mixed)
   *   Integer value of body type if numeric, string if not numeric.
   */
  public function getEncodingType($messageId, $numeric = false) {
    // See imap_fetchstructure() documentation for explanation.
    $encodings = array(
      0 => '7BIT',
      1 => '8BIT',
      2 => 'BINARY',
      3 => 'BASE64',
      4 => 'QUOTED-PRINTABLE',
      5 => 'OTHER',
    );

    // Get the structure of the message.
    $structure = $this->getStructure($messageId);

    // Return a number or a string, depending on the $numeric value.
    if ($numeric) {
      return $structure->encoding;
    } else {
      return $encodings[$structure->encoding];
    }
  }

  /**
   * Closes an active IMAP connection.
   *
   * @return (empty)
   */
  public function disconnect() {
    // Close the connection, deleting all messages marked for deletion.
    imap_close($this->mailbox, CL_EXPUNGE);
  }

  /**
   * Reconnect to the IMAP server.
   *
   * @return (empty)
   *
   * @throws Exception when IMAP can't reconnect.
   */
  private function reconnect() {
    $this->mailbox = imap_open($this->address, $this->user, $this->pass);
    if (!$this->mailbox) {
      throw new \Exception("Reconnection Failure: " . imap_last_error());
    }
  }

  /**
   * Checks to see if the connection is alive. If not, reconnects to server.
   *
   * @return (empty)
   */
  private function tickle() {
    if (!imap_ping($this->mailbox)) {
      $this->reconnect;
    }
  }

  /**
   * Determines whether the given message is from an auto-responder.
   *
   * This method checks whether the header contains any auto response headers as
   * outlined in RFC 3834, and also checks to see if the subject line contains
   * certain strings set by different email providers to indicate an automatic
   * response.
   *
   * @see http://tools.ietf.org/html/rfc3834
   *
   * @param $header (string)
   *   Message header as returned by imap_fetchheader().
   *
   * @return (bool)
   *   TRUE if this message comes from an autoresponder.
   */
  private function detectAutoresponder($header) {
    $autoresponder_strings = array(
      'X-Autoresponse:', // Other email servers.
      'X-Autorespond:', // LogSat server.
      'Subject: Auto Response', // Yahoo mail.
      'Out of office', // Generic.
      'Out of the office', // Generic.
      'out of the office', // Generic.
      'Auto-reply', // Generic.
      'Autoreply', // Generic.
      'autoreply', // Generic.
    );

    // Check for presence of different autoresponder strings.
    foreach ($autoresponder_strings as $string) {
      if (strpos($header, $string) !== false) {
        return true;
      }
    }

    return false;
  }

  private function is_base64($s){
    // Check if there are valid base64 characters
    if (!preg_match("/^([A-Za-z0-9+\/]{4})*([A-Za-z0-9+\/]{4}|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{2}==)$/", $s)) return false;

    // Decode the string in strict mode and check the results
    $decoded = base64_decode($s, true);
    if(false === $decoded) return false;

    // Encode the string again
    if(base64_encode($decoded) != $s) return false;

    return true;
  }
}
