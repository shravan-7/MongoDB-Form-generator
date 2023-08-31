<?php

defined('BASEPATH') or exit('No direct script access allowed');

class FormController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Forms_model');
        $this->load->model('Fields_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        // Load the Forms_model
        $this->load->model('Forms_model');

        // Handle form submission
        if ($this->input->post()) {
            // Collect form data
            $clg_id = $this->session->userdata('user_id');
            $form_title = $this->input->post('form_title');
            $form_description = $this->input->post('form_description');
            $field_labels = $this->input->post('field_label');
            $field_types = $this->input->post('field_type');
            $option_values = $this->input->post('option_value');
            $required = $this->input->post('field_required');
            $size_length = $this->input->post('size_length');


            // Create an array to hold the form data
            $form_data = array(
                'clg_id' => $clg_id,
                'form_title' => $form_title,
                'form_description' => $form_description,
                'fields' => array(),
            );

            for ($i = 0; $i < count($field_labels); $i++) {
                if (!($size_length[$i])) {
                    $size_length[$i] = 255;
                }
            }
            for ($i = 0; $i < count($field_labels); $i++) {

                $field = array(
                    'field_label' => $field_labels[$i],
                    'field_type' => $field_types[$i],
                    'field_required' =>  $required[$i],
                    'size_length' => $size_length[$i],
                );

                if (in_array($field_types[$i], array('Dropdown', 'Checkbox', 'Radio'))) {
                    $field_options = array();
                    for ($j = 0; $j < count($option_values[$i]); $j++) {
                        $field_options[] = $option_values[$i][$j];
                    }
                    $field['options'] = $field_options;
                }
                $form_data['fields'][] = $field;
            }

            $insert_result = $this->Forms_model->create_form($form_data);

            if ($insert_result) {
                //var_dump($option_values);
                redirect('display_form/' . urlencode($form_title));

            } else {
                //var_dump($option_values);
                redirect('home');
            }
        }

        // Load the view
        $this->load->view('templates/header');
        $this->load->view('form_builder');
        $this->load->view('templates/footer');
    }



    public function view_forms()
    {
        $data['forms'] = $this->Forms_model->get_all_forms();

        $this->load->view('templates/header');
        $this->load->view('forms_view', $data); // Create this view file
        $this->load->view('templates/footer');
    }
    public function display_form($form_title)
    {
        // Retrieve form details from MongoDB based on $form_id
        $form_data = $this->Forms_model->get_form_by_title($form_title);
        //var_dump($form_data);
        if ($form_data) {
            // Load the display_form view with form data
            $data['form_id'] = $form_data->_id;
            $data['form_title'] = $form_data->form_title;
            $data['form_description'] = $form_data->form_description;
            $data['form_fields'] = $form_data->fields;

            $this->load->view('display_form', $data);
        } else {
            // Handle form not found error
            show_error('Form not found', 404);
        }
    }

    public function edit_form($form_id)
    {
        // Retrieve form details from MongoDB based on $form_id
        $form_data = $this->Forms_model->get_form($form_id);

        if ($form_data) {
            // Load the form_builder view with form data for editing
            $data['form_id'] = $form_id;
            $data['form_title'] = $form_data->form_title;
            $data['form_description'] = $form_data->form_description;
            $data['form_fields'] = $form_data->fields;

            $this->load->view('form_builder', $data);
        } else {
            // Handle form not found error
            show_error('Form not found', 404);
        }
    }


}
