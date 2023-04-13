<?php declare(strict_types=0);
/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * @var CView $this
 * @var array $data
 */

$csrf_token = CCsrfTokenHelper::get('discovery');

// Create form.
$form = (new CForm())
	->addItem((new CVar(CCsrfTokenHelper::CSRF_TOKEN_NAME, $csrf_token))->removeId())
	->setName('discoveryForm')
	->setId('discoveryForm')
	->addItem((new CInput('submit', null))->addStyle('display: none;'));

if (!empty($this->data['drule']['druleid'])) {
	$form->addVar('druleid', $this->data['drule']['druleid']);
}

// Create form grid.
$discoveryFormGrid = (new CFormGrid())
	->addItem([
		(new CLabel(_('Name'), 'name'))->setAsteriskMark(),
		(new CTextBox('name', $this->data['drule']['name']))
			->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
			->setAriaRequired()
			->setAttribute('autofocus', 'autofocus')
	]);

// Append proxy to form list.
$proxy_select = (new CSelect('proxy_hostid'))
	->setValue($this->data['drule']['proxy_hostid'])
	->setFocusableElementId('label-proxy')
	->addOption(new CSelectOption(0, _('No proxy')));

foreach ($this->data['proxies'] as $proxy) {
	$proxy_select->addOption(new CSelectOption($proxy['proxyid'], $proxy['host']));
}

$discoveryFormGrid
	->addItem([new CLabel(_('Discovery by proxy'), $proxy_select->getFocusableElementId()), $proxy_select])
	->addItem([(new CLabel(_('IP range'), 'iprange'))->setAsteriskMark(),
		(new CTextArea('iprange', $this->data['drule']['iprange'], ['maxlength' => 2048]))
			->addStyle('max-width: '.ZBX_TEXTAREA_BIG_WIDTH.'px')
			->setAriaRequired()
	])
	->addItem([(new CLabel(_('Update interval'), 'delay'))->setAsteriskMark(),
		(new CTextBox('delay', $data['drule']['delay']))
			->setWidth(ZBX_TEXTAREA_SMALL_WIDTH)
			->setAriaRequired()
	]);

$discoveryFormGrid->addItem([
	(new CLabel(_('Checks'), 'dcheckList'))->setAsteriskMark(),
	new CFormField(
		(new CDiv(
			(new CTable())
				->setAttribute('style', 'width: 100%;')
				->setHeader([_('Type'), _('Actions')])
				->addItem(
					(new CTag('tfoot', true))
						->addItem(
							(new CCol(
								(new CSimpleButton(_('Add')))
									->setAttribute('data-action', 'add')
									->addClass(ZBX_STYLE_BTN_LINK)
									->addClass('js-check-add')
							))->setColSpan(2)
						)
				)->setId('dcheckListFooter')
		))
			->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
			->setAttribute('style', 'width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
			->setId('dcheckList')
	)
]);

// Append uniqueness criteria to form list.
$discoveryFormGrid->addItem([
	new CLabel(_('Device uniqueness criteria')),
	(new CDiv(
		(new CRadioButtonList('uniqueness_criteria', (int) $this->data['drule']['uniqueness_criteria']))
			->setId('device-uniqueness-list')
			->makeVertical()
			->addValue(_('IP address'), -1, zbx_formatDomId('uniqueness_criteria_ip'))
	))
		->setAttribute('style', 'width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
		->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
]);

$uniqueness_template = (new CTemplateTag('unique-row-tmpl'))->addItem(
	(new CListItem([
		(new CInput('radio', 'uniqueness_criteria', '#{dcheckid}'))
			->addClass(ZBX_STYLE_CHECKBOX_RADIO)
			->setId('uniqueness_criteria_#{dcheckid}'),
		(new CLabel([new CSpan(), '#{name}'], 'uniqueness_criteria_#{dcheckid}'))->addClass(ZBX_STYLE_WORDWRAP)
	]))
		->setId('uniqueness_criteria_row_#{dcheckid}')
);

// Append host source to form list.
$discoveryFormGrid->addItem([
	new CLabel(_('Host name')),
	(new CDiv(
		(new CRadioButtonList('host_source', (int) $this->data['drule']['host_source']))
			->makeVertical()
			->addValue(_('DNS name'), ZBX_DISCOVERY_DNS, 'host_source_chk_dns')
			->addValue(_('IP address'), ZBX_DISCOVERY_IP, 'host_source_chk_ip')
			->setId('host_source')
	))
		->setAttribute('style', 'width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
		->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
]);

$host_source_template = (new CTemplateTag('host-source-row-tmpl'))->addItem(
	(new CListItem([
		(new CInput('radio', 'host_source', '_#{dcheckid}'))
			->addClass(ZBX_STYLE_CHECKBOX_RADIO)
			->setAttribute('data-id', '#{dcheckid}')
			->setId('host_source_#{dcheckid}'),
		(new CLabel([new CSpan(), '#{name}'], 'host_source_#{dcheckid}'))->addClass(ZBX_STYLE_WORDWRAP)
	]))
		->setId('host_source_row_#{dcheckid}')
);

// Append name source to form list.
$discoveryFormGrid->addItem([
	new CLabel(_('Visible name')),
	(new CDiv(
		(new CRadioButtonList('name_source', (int) $this->data['drule']['name_source']))
			->makeVertical()
			->addValue(_('Host name'), ZBX_DISCOVERY_UNSPEC, 'name_source_chk_host')
			->addValue(_('DNS name'), ZBX_DISCOVERY_DNS, 'name_source_chk_dns')
			->addValue(_('IP address'), ZBX_DISCOVERY_IP, 'name_source_chk_ip')
			->setId('name_source')
	))
		->setAttribute('style', 'width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
		->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
]);

$name_source_template = (new CTemplateTag('name-source-row-tmpl'))->addItem(
	(new CListItem([
		(new CInput('radio', 'name_source', '_#{dcheckid}'))
			->addClass(ZBX_STYLE_CHECKBOX_RADIO)
			->setAttribute('data-id', '#{dcheckid}')
			->setId('name_source_#{dcheckid}'),
		(new CLabel([new CSpan(), '#{name}'], 'name_source_#{dcheckid}'))->addClass(ZBX_STYLE_WORDWRAP)
	]))
		->setId('name_source_row_#{dcheckid}')
);

$discoveryFormGrid->addItem([
	new CLabel(_('Enabled'), 'status'),
	new CFormField((new CCheckBox('status', DRULE_STATUS_ACTIVE))
		->setUncheckedValue(DRULE_STATUS_DISABLED)
		->setChecked($this->data['drule']['status'] == DRULE_STATUS_ACTIVE)
	)
]);

$check_template_default = (new CTemplateTag('dcheck-row-tmpl'))->addItem(
	(new CRow([
		(new CCol('#{name}'))
			->addClass(ZBX_STYLE_WORDWRAP)
			->addStyle(ZBX_TEXTAREA_BIG_WIDTH)
			->setId('dcheckCell_#{dcheckid}'),
		new CHorList([
			(new CButton(null, _('Edit')))
				->addClass(ZBX_STYLE_BTN_LINK)
				->addClass('js-edit'),
			(new CButton(null, _('Remove')))
				->addClass(ZBX_STYLE_BTN_LINK)
				->addClass('js-remove')
		])
	]))
		->setId('dcheckRow_#{dcheckid}')
		->setAttribute('dcheckRow', '#{dcheckid}')
);

$form
	->addItem($discoveryFormGrid)
	->addItem($check_template_default)
	->addItem($uniqueness_template)
	->addItem($host_source_template)
	->addItem($name_source_template)
	->addItem(
		(new CScriptTag('
			drule_edit_popup.init('.json_encode([
				'druleid' => $data['drule']['druleid'],
				'dchecks' => $data['drule']['dchecks'],
				'drule' => $data['drule']
			], JSON_THROW_ON_ERROR).');
		'))->setOnDocumentReady()
	);

if ($data['drule']['druleid']) {
	$buttons = [
		[
			'title' => _('Update'),
			'keepOpen' => true,
			'isSubmit' => true,
			'action' => 'drule_edit_popup.submit();'
		],
		[
			'title' => _('Clone'),
			'class' => ZBX_STYLE_BTN_ALT,
			'keepOpen' => true,
			'isSubmit' => false,
			'action' => 'drule_edit_popup.clone('.json_encode([
					'title' => _('New discovery rule'),
					'buttons' => [
						[
							'title' => _('Add'),
							'class' => 'js-add',
							'keepOpen' => true,
							'isSubmit' => true,
							'action' => 'drule_edit_popup.submit();'
						],
						[
							'title' => _('Cancel'),
							'class' => ZBX_STYLE_BTN_ALT,
							'cancel' => true,
							'action' => ''
						]
					]
				]).');'
		],
		[
			'title' => _('Delete'),
			'class' => ZBX_STYLE_BTN_ALT,
			'keepOpen' => true,
			'isSubmit' => false,
			'action' => 'drule_edit_popup.delete();'
		]
	];
}
else {
	$buttons = [
		[
			'title' => _('Add'),
			'class' => 'js-add',
			'keepOpen' => true,
			'isSubmit' => true,
			'action' => 'drule_edit_popup.submit();'
		]
	];
}

$output = [
	'header' => $data['drule']['druleid'] ? _('Discovery rule') : _('New discovery rule'),
	'doc_url' => CDocHelper::getUrl(CDocHelper::DATA_COLLECTION_DISCOVERY_EDIT),
	'body' => $form->toString(),
	'buttons' => $buttons,
	'script_inline' => getPagePostJs().
		$this->readJsFile('configuration.discovery.edit.js.php')
];

echo json_encode($output);
