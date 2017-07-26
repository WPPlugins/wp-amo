'use strict';

jQuery(function ($) {
    const dateFmt = 'MM/DD/YYYY HH:mm:ss';

    $('.fullcalendar').each(function () {
        const $calendar = $(this);
        const baseURL   = $calendar.data('baseUrl');
        const apiKey    = $calendar.data('apiKey');

        var eventSources = [];
        if ($calendar.data('showEvents')) {
            var params = {
                apikey: apiKey,
            };

            const eventType = $calendar.data('eventType');

            if (eventType) {
                params.pk_association_event_type = eventType;
            }

            eventSources.push({
                url:                baseURL + 'AMOEvents',
                startParam:         'date_start',
                endParam:           'date_end',
                eventDataTransform: function (eventData) {
                    return {
                        id:              eventData['pk_association_event'],
                        type:            'event',
                        title:           eventData['event_name'],
                        start:           moment(eventData['date_start'], dateFmt),
                        end:             moment(eventData['date_end'], dateFmt),
                        url:             eventData['registration_link'],
                        textColor:       getTextColor(eventData['event_calendar_color']),
                        backgroundColor: '#' + eventData['event_calendar_color'] || '00007f',
                        detailLink:      baseURL + 'AMOEvents/' + eventData['pk_association_event'],
                    };
                },
                type:               'GET',
                data:               params,
                error:              function (err) {
                    errorDialog('Unable to load events. Please try again later.');
                    console.error('Error fetching events:', err);
                },
            });
        }

        if ($calendar.data('showAnnouncements')) {
            eventSources.push({
                url:                baseURL + 'AMOAnnouncements',
                startParam:         'announcement_start_date',
                endParam:           'announcement_end_date',
                eventDataTransform: function (eventData) {
                    return {
                        id:              eventData['pk_association_announcement'],
                        type:            'announcement',
                        title:           eventData['announcement_title'],
                        start:           moment(eventData['announcement_date_start'], dateFmt),
                        end:             moment(eventData['announcement_date_end'], dateFmt),
                        textColor:       getTextColor(eventData['announcement_calendar_color']),
                        backgroundColor: '#' + eventData['announcement_calendar_color'] || '00007f',
                        detailLink:      baseURL + 'AMOAnnouncements/' + eventData['pk_association_announcement'],
                    };
                },
                type:               'GET',
                data:               {
                    apikey: apiKey,
                },
                error:              function (err) {
                    errorDialog('Unable to load announcements. Please try again later.');
                    console.error('Error fetching announcements:', err);
                },
            });
        }

        $calendar.fullCalendar({
            defaultView:  getDefaultView(),
            header:       {
                left:  'title',
                right: 'prev,next today'
            },
            height:       'auto',
            editable:     false,
            eventSources: eventSources,
            eventClick:   function (event) {
                $('#overlay').fadeIn();
                // Only show spinner if XHR takes too long to avoid flicker
                var overlay = setTimeout(function () {
                    overlay = 0;
                    $('#spinner').fadeIn();
                }, 250);

                if (event.id) {
                    $.getJSON(event.detailLink, {apikey: apiKey})
                        .then(function (response) {
                            const fullEvent = response[0];
                            if (event.type === 'event') {
                                $('#event-desc').html(fullEvent.event_desc);
                                if (fullEvent.registration_link) {
                                    $('#event-link').show().find('a').attr('href', fullEvent.registration_link);
                                }
                            } else {
                                $('#event-desc').html(fullEvent.announcement_description);
                            }

                            if (overlay === 0) {
                                $('#spinner').hide();
                            } else {
                                clearTimeout(overlay);
                            }

                            $('#dialog')
                                .dialog({
                                    appendTo: $calendar,
                                    modal:    true,
                                    title:    event.title,
                                    width:    800,
                                    // height:   200,
                                    close:    function () {
                                        $('#event-link').hide().find('a').attr('href', null);
                                    },
                                });

                            // Dialog shows its own overlay
                            $('#overlay').hide();
                        });
                }

                return false;
            },
            windowResize: function (view) {
                view.calendar.changeView(getDefaultView());
            },
        })
    });

    function getDefaultView() {
        if ($(window).width() < 544) {
            return 'listMonth';
        } else {
            return 'month';
        }
    }

    function errorDialog(message) {
        $('#event-desc').html(message);
        $('#dialog')
            .dialog({
                modal: true,
                title: 'Error',
            });
    }

    /**
     * Determine whether foreground should be black or white based on background color.
     *
     * @param {string} bg Hex color (e.g. 1a2b3c)
     * @returns {string}
     */
    function getTextColor(bg) {
        var color = '#fff', bits;

        if (bg) {
            bits = /^(\w{2})(\w{2})(\w{2})$/.exec(String(bg).replace(/ /g, '').toLowerCase());
        }

        if (bits) {
            const rgb  = {
                r: parseInt(bits[1], 16),
                g: parseInt(bits[2], 16),
                b: parseInt(bits[3], 16),
            };
            const luma = (((0.299 * rgb.r) + ((0.587 * rgb.g) + (0.114 * rgb.b))) / 255);
            color      = luma > 0.5 ? '#000' : '#fff';
        }

        return color;
    }
});
