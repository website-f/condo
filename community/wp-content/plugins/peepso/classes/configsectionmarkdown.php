<?php

class PeepSoConfigSectionMarkdown extends PeepSoConfigSectionAbstract
{
	// Builds the groups array
	public function register_config_groups()
	{
		$this->context='left';
		$this->left();

        if(class_exists('PeepSoGroupsPlugin')) {
            $this->groups();
        }

        if(class_exists('PeepSoPagesPlugin')) {
            $this->pages();
        }

		$this->context='right';
        if(class_exists('PeepSoMessages')) {
            #$this->chat();
        }



        $this->readme();
	}


	/**
	 * General Settings Box
	 */
	private function left()
	{
        $this->set_field(
            'md_post',
            __('Enable in posts','peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'md_comment',
            __('Enable in comments','peepso-core'),
            'yesno_switch'
        );


        $this->args('default',1);
        $this->args('descript', __('ON: users can use syntax generating &lt;h&gt; tags', 'peepso-core'));
        $this->set_field(
            'md_headers',
            __('Allow headers','peepso-core'),
            'yesno_switch'
        );


        $this->args('default', 1);
        $this->args('descript', __('ON: replace the default MarkDown &lt;p&gt; tag rendering with &lt;br&gt; tags', 'peepso-core'));
        $this->set_field(
            'md_no_paragraph',
            __('Use regular linebreaks', 'peepso-core'),
            'yesno_switch'
        );

		$this->set_group(
			'peepso_md_general',
			__('General', 'peepsommd')
		);
	}

	public function chat() {
        $this->set_field(
            'md_chat',
            __('Enable in messages','peepso-core'),
            'yesno_switch'
        );

        $this->set_group(
            'peepso_md_chat',
            __('Chat', 'peepsommd')
        );
    }

    public function groups()
    {
        $this->set_field(
            'md_groups_about',
            __('Enable in group descriptions','peepso-core'),
            'yesno_switch'
        );


//        $this->set_field(
//            'md_groups_rules',
//            __('Enable in group rules','peepso-core'),
//            'yesno_switch'
//        );

        $this->set_group(
            'md_groups',
            __('Groups', 'peepso-core')
        );
    }

    public function pages()
    {
        $this->set_field(
            'md_pages_about',
            __('Enable in page descriptions','peepso-core'),
            'yesno_switch'
        );


//        $this->set_field(
//            'md_pages_rules',
//            __('Enable in page rules','peepso-core'),
//            'yesno_switch'
//        );

        $this->set_group(
            'md_pages',
            __('Pages', 'peepso-core')
        );
    }

    public function readme() {

        $this->set_field(
            'md_readme',
            __('Markdown is a lightweight markup language with plain text formatting syntax. It is designed so that it can be converted to HTML and many other formats', 'peepso-core') . ' ' .
            '(<a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">Learn more about it here.</a>) You can copy the entirety of the text below and paste it as a post or a comment to see what it shows up as / renders as within PeepSo. Please note that tables need to be posted separately for them to work best. <b>Markdown is only supported within: PeepSo Posts, Comments, Groups and Pages descriptions. </b>You can see the supported syntax below: <br/>' .
            '<br/>' .
            '<b>Headers:</b><br/>' .
            '# Header_text - for <b>h1</b><br/>' .
            '## Header_text - for <b>h2</b><br/>' .
            '### Header_text - for <b>h3</b><br/>' .
            '#### Header_text - for <b>h4</b><br/>' .
            '##### Header_text - for <b>h5</b><br/>' .
            '###### Header_text - for <b>h6</b><br/><br/>' .

            '<b>Basic text formatting:</b><br/>' .
            '**bold text** for <b>bold text</b><br/>' .
            '*italic text* for <i>italic text</i><br/>' .
            '~~strikethrough~~ for <del>strikethrough</del><br/><br/>' .
            
            '<b>Sharing inline code:</b><br/>' .
            '`inline code` for <code>inline code</code><br/><br/>' .
            '<b>Sharing code block:</b><br/>' .
            '```<br/>function incrementNumber($number) {<br/>&nbsp;&nbsp;&nbsp;&nbsp;return $number * 2;<br/>}<br/>```<br/><br/>```php<br/>function incrementNumber($number) {<br/>&nbsp;&nbsp;&nbsp;&nbsp;return $number * 2;<br/>}<br/>```<br/><br/>```sql<br/>SELECT * FROM EMP JOIN DEPT ON EMP.DEPTNO = DEPT.DEPTNO;<br/>```<br/><br/>```css<br/>.class-name {<br/>&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br/>}<br/>```<br/><br/>' .

            '<b>Unordered and Ordered lists:</b><br/>' .
            '<b>Unordered:</b><br/>Subitems need 3 spaces and then a dash for it to work.<br/>- Item 1<br/>- Item 2<br/>&nbsp;&nbsp;&nbsp;- Sub-item 1<br/>&nbsp;&nbsp;&nbsp;- Sub-item 2<br/><br/>' .
            '<b>Ordered:</b><br/>Subitems need 3 spaces and then a number for it to work.<br/>1. Item 1<br/>2. Item 2<br/>&nbsp;&nbsp;&nbsp;1. Sub-item 1<br/>&nbsp;&nbsp;&nbsp;2. Sub-item 2<br/><br/>' .
            
            
            '<b>Links and Images:</b><br/>' .
            '[link](https://PeepSo.com)` for <a href="https://PeepSo.com">link</a><br/>' .
            '![Alt text](https://peepso.com/wp-content/uploads/2024/05/logo.svg) - for embedding images from other sources. These images will NOT be saved within your website. They are just linked to.<br/><br/>' .

            '<b>Tables:</b><br/>' .
            'Tables consist of cetain elements a simple dash "-" and a "|" (vertical bar, pipe, or or symbol)<br/><br/>' .
            '| Header 1 | Header 2 | Header 3 |<br/>|---|---|---|<br/>| Row 1    | Data     | Data     |<br/>| Row 2    | Data     | Data     |<br/><br/>' .
            '<b>Correct Formatting Rules for Tables:</b><br/>Pipes (|) must separate columns.<br/>The second row must contain dashes (---) to define the table.<br/>Extra spaces are allowed but not required.<br/>Aligning spaces only help readability and are optional.<br/><br/>' .
            
            '<b>Tasks / Checkboxes:</b><br/>' .
            'When used, you will see checked and unchecked checkboxes. They can only be clicked / checked or unchecked by editing the text. They can not be clicked.<br/>' .
            '- [x] Completed task<br/>- [ ] Incomplete task<br/>' .
            
            ''
            ,
            'message'
        );

        $this->set_group(
            'peepso_md_groups',
            __('About Markdown', 'peepso-core')
        );
    }
}