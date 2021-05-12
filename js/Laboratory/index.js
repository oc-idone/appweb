'use strict';

$(document).ready(function()
{
    $('[data-search="custody_chains"]').focus();

    $('[data-search="custody_chains"]').on('keyup', function()
    {
        search_in_table($(this).val(), $('[data-table="custody_chains"]').find(' > tbody > tr'));
    });

    var filter_action = 'filter_custody_chains';
    var restore_action = 'restore_custody_chain';
    var empty_action = 'empty_custody_chains';
    var delete_action = 'delete_custody_chain';

    $(document).on('click', '[data-action="' + filter_action + '"]', function()
    {
        action = filter_action;
        id = null;

        transform_form_modal('filter', $('[data-modal="' + filter_action + '"]'));
        open_form_modal('filter', $('[data-modal="' + filter_action + '"]'));
    });

    $('[name="own"]').on('change', function()
    {
        $('[name="taker"]').val('all');
        $('[name="collector"]').val('all');
        $('[name="sent_status"]').val('all');

        if ($(this).val() == 'account')
        {
            $(this).parent().parent().removeClass('span4');
            $(this).parent().parent().addClass('span12');
            $('[name="taker"]').parent().parent().addClass('hidden');
            $('[name="collector"]').parent().parent().addClass('hidden');
            $('[name="sent_status"]').parent().parent().addClass('hidden');
            $('[name="deleted_status"]').parent().parent().removeClass('span6');
            $('[name="deleted_status"]').parent().parent().addClass('span12');
        }
        else
        {
            $(this).parent().parent().removeClass('span12');
            $(this).parent().parent().addClass('span4');
            $('[name="taker"]').parent().parent().removeClass('hidden');
            $('[name="collector"]').parent().parent().removeClass('hidden');

            if ($('[name="deleted_status"]').val() != 'deleted')
            {
                $('[name="sent_status"]').parent().parent().removeClass('hidden');
                $('[name="deleted_status"]').parent().parent().removeClass('span12');
                $('[name="deleted_status"]').parent().parent().addClass('span6');
            }
        }
    });

    $('[name="deleted_status"]').on('change', function()
    {
        $('[name="sent_status"]').val('all');

        if ($(this).val() == 'deleted')
        {
            $('[name="start_date"]').parents('fieldset').addClass('hidden');
            $('[name="start_hour"]').parents('fieldset').addClass('hidden');
            $('[name="sent_status"]').parent().parent().addClass('hidden');
            $(this).parent().parent().removeClass('span6');
            $(this).parent().parent().addClass('span12');
        }
        else
        {
            $('[name="start_date"]').parents('fieldset').removeClass('hidden');
            $('[name="start_hour"]').parents('fieldset').removeClass('hidden');

            if ($('[name="own"]').val() != 'account')
            {
                $('[name="sent_status"]').parent().parent().removeClass('hidden');
                $(this).parent().parent().removeClass('span12');
                $(this).parent().parent().addClass('span6');
            }
        }
    });

    $('[data-modal="' + filter_action + '"]').find('form').on('submit', function(event)
    {
        action = filter_action;
        id = null;

        send_form_modal('filter', $(this), event);
    });

    $(document).on('click', '[data-action="' + restore_action + '"]', function()
    {
        action = restore_action;
        id = $(this).data('id');

        send_form_modal('restore');
    });

    $(document).on('click', '[data-action="' + empty_action + '"]', function()
    {
        action = empty_action;
        id = null;

        open_form_modal('delete', $('[data-modal="' + delete_action + '"]'));
    });

    $(document).on('click', '[data-action="' + delete_action + '"]', function()
    {
        action = delete_action;
        id = $(this).data('id');

        open_form_modal('delete', $('[data-modal="' + delete_action + '"]'));
    });

    $('[data-modal="' + delete_action + '"]').modal().onSuccess(function()
    {
        send_form_modal('delete');
    });
});
