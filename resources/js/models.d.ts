/**
 * This file is auto generated using 'php artisan typescript:generate'
 *
 * Changes to this file will be lost when the command is run again
 */

declare namespace App.Models {
    export interface Assignment {
        id: number;
        user_id: number;
        title: string | null;
        description: string | null;
        link: string | null;
        icon: string | null;
        resolved_at: string | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Car {}

    export interface CertificationTest {
        id: number;
        description: string;
        status: string;
        enabled: boolean;
        premium: boolean;
        average: number | null;
        order_id: number | null;
        order_course_id: number | null;
        price_id: number | null;
        price: number | null;
        currency_id: number | null;
        payment_method_id: number | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface City {
        id: number;
        name: string;
        state_id: number;
        state_code: string;
        country_id: number;
        country_code: string;
        latitude: number;
        longitude: number;
        created_at: string;
        updated_at: string;
        flag: boolean;
        wikiDataId: string | null;
    }

    export interface Contry {}

    export interface Country {
        id: number;
        name: string;
        iso3: string | null;
        numeric_code: string | null;
        iso2: string | null;
        phonecode: string | null;
        capital: string | null;
        currency: string | null;
        currency_name: string | null;
        currency_symbol: string | null;
        tld: string | null;
        native: string | null;
        region: string | null;
        region_id: number | null;
        subregion: string | null;
        subregion_id: number | null;
        nationality: string | null;
        timezones: string | null;
        translations: string | null;
        latitude: number | null;
        longitude: number | null;
        emoji: string | null;
        emojiU: string | null;
        created_at: string | null;
        updated_at: string;
        flag: boolean;
        wikiDataId: string | null;
        cities?: Array<App.Models.City> | null;
        states?: Array<App.Models.State> | null;
        cities_count?: number | null;
        states_count?: number | null;
    }

    export interface Course {
        id: number;
        name: string;
        short_name: string;
        description: string | null;
        type: string;
        wp_post_id: number | null;
        created_at: string | null;
        updated_at: string | null;
        prices?: Array<App.Models.Price> | null;
        wp_learnpress_user_item?: App.Models.Wordpress.WpLearnpressUserItem | null;
        prices_count?: number | null;
    }

    export interface CourseDateHistory {}

    export interface CoursePrice {
        id: number;
        course_id: number | null;
        price_id: number | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Currency {
        id: number;
        iso_code: string;
        name: string;
        symbol: string;
        position: string;
        country_id: number | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface DatesHistory {
        id: number;
        order_course_id: number | null;
        order_id: number | null;
        start_date: string | null;
        end_date: string | null;
        type: string | null;
        extension_id: number | null;
        freezing_id: number | null;
        created_at: string | null;
        updated_at: string | null;
        course?: App.Models.Course | null;
    }

    export interface DocumentType {
        id: number;
        name: string;
        code: string | null;
        description: string | null;
        custom: boolean;
        country_id: number | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Due {
        id: number;
        order_id: number | null;
        date: string;
        amount: number | null;
        paid: boolean | null;
        payment_method_id: number | null;
        currency_id: number | null;
        payment_receipt: string | null;
        position: number | null;
        created_at: string | null;
        updated_at: string | null;
        payment_method?: App.Models.PaymentMethod | null;
        currency?: App.Models.Currency | null;
    }

    export interface EspecialMessage {
        id: number;
        name: string;
        remaining: string;
        content: string;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Extension {
        id: number;
        months: number | null;
        order_id: number | null;
        order_course_id: number | null;
        price_id: number | null;
        price_amount: number | null;
        payment_date: string | null;
        currency_id: number | null;
        payment_method_id: number | null;
        draft: boolean;
        created_at: string | null;
        updated_at: string | null;
        order_course?: App.Models.OrderCourse | null;
    }

    export interface Freezing {
        id: number;
        months: number | null;
        start_date: string | null;
        finish_date: string | null;
        new_finish_date: string | null;
        return_date: string | null;
        new_return_date: string | null;
        payment_date: string | null;
        price_id: number | null;
        price_amount: number | null;
        currency_id: number | null;
        payment_method_id: number | null;
        order_id: number | null;
        order_course_id: number | null;
        remain_license: string | null;
        mail_status: string;
        mail_id: string | null;
        mail_status_unfreeze: string;
        mail_unfreeze_id: string | null;
        created_at: string | null;
        updated_at: string | null;
        order_course?: App.Models.OrderCourse | null;
    }

    export interface Holiday {
        id: number;
        name: string | null;
        date: string;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Invoice {
        id: number;
        requested: boolean;
        ruc: string | null;
        business_name: string | null;
        email: string | null;
        tax_situation_proof: string | null;
        tax_situation: string | null;
        tax_regime: string | null;
        address: string | null;
        postal_code: string | null;
        cellphone: string | null;
        cfdi_use: string | null;
        type: string | null;
        order_id: number | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Lead {
        id: number;
        name: string;
        courses: string;
        phone: string;
        email: string | null;
        origin: string | null;
        city_id: number | null;
        state_id: number | null;
        country_id: number | null;
        document_type_id: number | null;
        document: string | null;
        user_id: number | null;
        channel_id: number | null;
        chat_date: string | null;
        lead_project_id: number | null;
        created_at: string | null;
        updated_at: string | null;
        status: string;
        lead_assignments?: Array<App.Models.LeadAssignment> | null;
        observations?: Array<App.Models.LeadObservation> | null;
        user?: App.Models.User | null;
        lead_project?: App.Models.LeadProject | null;
        sale_activities?: Array<App.Models.SaleActivity> | null;
        student?: App.Models.Student | null;
        zadarma_statistics?: Array<App.Models.ZadarmaStatistic> | null;
        lead_assignments_count?: number | null;
        observations_count?: number | null;
        sale_activities_count?: number | null;
        zadarma_statistics_count?: number | null;
    }

    export interface LeadAssignment {
        id: number;
        lead_id: number;
        user_id: number;
        assigned_at: string;
        order: number;
        active: boolean;
        round: boolean;
        project_id: number | null;
        created_at: string | null;
        updated_at: string | null;
        user?: App.Models.User | null;
        lead?: App.Models.Lead | null;
        observations?: Array<App.Models.LeadObservation> | null;
        comunications?: Array<App.Models.SaleActivity> | null;
        calls?: Array<App.Models.SaleActivity> | null;
        sale_activities?: Array<App.Models.SaleActivity> | null;
        observations_count?: number | null;
        comunications_count?: number | null;
        calls_count?: number | null;
        sale_activities_count?: number | null;
    }

    export interface LeadObservation {
        id: number;
        user_id: number | null;
        lead_id: number | null;
        lead_assignment_id: number | null;
        call_status: string;
        schedule_call_datetime: string | null;
        observation: string | null;
        created_at: string | null;
        updated_at: string | null;
        user?: App.Models.User | null;
        lead_assignment?: App.Models.LeadAssignment | null;
        readonly date?: any;
        readonly time?: any;
    }

    export interface LeadProject {
        id: number;
        name: string;
        created_at: string | null;
        updated_at: string | null;
        leads?: Array<App.Models.Lead> | null;
        users?: Array<App.Models.User> | null;
        leads_count?: number | null;
        users_count?: number | null;
    }

    export interface LiveConnectRequest {
        id: number;
        headers: string;
        body: string;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Message {
        id: number;
        name: string;
        content: string;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Module {
        id: number;
        name: string;
        description: string | null;
        icon: string;
        path: string;
        type: string;
        parent_id: number | null;
    }

    export interface ModuleRole {}

    export interface Notification {
        id: number;
        title: string;
        body: string | null;
        icon: string;
        url: string;
        read: boolean;
        use_router: boolean;
        user_id: number;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface OderDateHistory {}

    export interface Order {
        id: number;
        student_id: number | null;
        currency_id: number | null;
        enrollment_sheet: boolean | null;
        payment_mode: string;
        comunication_type: string | null;
        free_courses_date: string | null;
        price_id: number | null;
        price_amount: number;
        observation: string | null;
        created_by: number | null;
        updated_by: number | null;
        key: string | null;
        terms_confirmed_by_student: boolean;
        welcome_mail_id: string | null;
        observations: string | null;
        created_at: string | null;
        updated_at: string | null;
        order_courses?: Array<App.Models.OrderCourse> | null;
        student?: App.Models.Student | null;
        currency?: App.Models.Currency | null;
        dues?: Array<App.Models.Due> | null;
        user?: App.Models.User | null;
        invoice?: App.Models.Invoice | null;
        price?: App.Models.Price | null;
        sap_instalations?: Array<App.Models.SapInstalation> | null;
        order_courses_count?: number | null;
        dues_count?: number | null;
        sap_instalations_count?: number | null;
    }

    export interface OrderCourse {
        id: number;
        course_id: number | null;
        order_id: number | null;
        classroom_status: string;
        license: string | null;
        type: string | null;
        start: string | null;
        end: string | null;
        sap_user: string | null;
        enabled: boolean;
        certification_status: boolean;
        observation: string | null;
        welcome_mail_id: string | null;
        created_at: string | null;
        updated_at: string | null;
        course?: App.Models.Course | null;
        certification_tests?: Array<App.Models.CertificationTest> | null;
        freezings?: Array<App.Models.Freezing> | null;
        extensions?: Array<App.Models.Extension> | null;
        sap_instalations?: Array<App.Models.SapInstalation> | null;
        date_history?: Array<App.Models.DatesHistory> | null;
        order?: App.Models.Order | null;
        certification_tests_count?: number | null;
        freezings_count?: number | null;
        extensions_count?: number | null;
        sap_instalations_count?: number | null;
        date_history_count?: number | null;
    }

    export interface Payment {
        id: number;
        date: string;
        amount: number;
        payment_method_id: number | null;
        reference: string;
        observation: string;
        paid: boolean;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface PaymentMethod {
        id: number;
        name: string;
        description: string | null;
        currency_id: number | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Price {
        id: number;
        description: string | null;
        currency_id: number | null;
        amount: number;
        months: string;
        mode: string;
        created_at: string | null;
        updated_at: string | null;
        courses?: Array<App.Models.Course> | null;
        currency?: App.Models.Currency | null;
        courses_count?: number | null;
    }

    export interface Process {
        id: number;
        name: string;
        status: string;
        data: Array<any> | any | null;
        command: string;
        related_entity: string;
        related_entity_id: number;
        datetime_to_execute: string | null;
        finished_at: string | null;
        failed_at: string | null;
        failed_reason: string | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Role {
        id: number;
        name: string;
        description: string | null;
        created_at: string | null;
        updated_at: string | null;
        modules?: Array<App.Models.Module> | null;
        users?: Array<App.Models.User> | null;
        modules_count?: number | null;
        users_count?: number | null;
    }

    export interface SaleActivity {
        id: number;
        type: string | null;
        start: string | null;
        end: string | null;
        answered: boolean;
        observation: string | null;
        schedule_call_datetime: string | null;
        user_id: number | null;
        lead_id: number | null;
        lead_assignment_id: number | null;
        created_at: string | null;
        updated_at: string | null;
        lead?: App.Models.Lead | null;
        user?: App.Models.User | null;
        lead_assignment?: App.Models.LeadAssignment | null;
        readonly duration?: any;
    }

    export interface SapInstalation {
        id: number;
        key: string | null;
        order_id: number | null;
        operating_system: string | null;
        sap_user: string | null;
        pc_type: string | null;
        status: string | null;
        order_course_id: number | null;
        instalation_type: string | null;
        price_id: number | null;
        price_amount: number | null;
        payment_date: string | null;
        currency_id: number | null;
        payment_method_id: number | null;
        payment_receipt: string | null;
        observation: string | null;
        previus_sap_instalation: boolean | null;
        screenshot: string | null;
        restrictions: string | null;
        payment_enabled: boolean;
        payment_verified_at: string | null;
        payment_verified_by: number | null;
        draft: boolean;
        created_at: string | null;
        updated_at: string | null;
        student?: App.Models.Student | null;
        sap_tries?: Array<App.Models.SapTry> | null;
        order_course?: App.Models.OrderCourse | null;
        order?: App.Models.Order | null;
        sap_tries_count?: number | null;
        readonly start_datetime?: any;
        readonly start_datetime_target_timezone?: any;
        readonly end_datetime?: any;
        readonly staff_id?: any;
        readonly time?: any;
        readonly date?: any;
        readonly timezone?: any;
        readonly last_try_status?: any;
    }

    export interface SapInstalationStatus {}

    export interface SapInstalationType {
        id: number;
        name: string;
        description: string | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface SapTry {
        id: number;
        sap_instalation_id: number;
        start_datetime: string | null;
        start_datetime_target_timezone: string | null;
        timezone: string | null;
        end_datetime: string | null;
        status: string | null;
        staff_id: number | null;
        schedule_at: string | null;
        zoho_data: Array<any> | any | null;
        price_id: number | null;
        price_amount: number | null;
        currency_id: number | null;
        payment_method_id: number | null;
        payment_date: string | null;
        payment_receipt: string | null;
        payment_enabled: boolean;
        payment_verified_at: string | null;
        payment_verified_by: number | null;
        created_at: string | null;
        updated_at: string | null;
        sap_instalation?: App.Models.SapInstalation | null;
        staff?: App.Models.User | null;
        readonly time?: any;
        readonly date?: any;
    }

    export interface Sheet {
        id: number;
        sheet_id: string;
        base_tab_id: number;
        course_tab_id: number;
        link: string;
        type: string;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Shoe {}

    export interface StaffAvailabilitySlot {
        id: number;
        user_id: number;
        day: string;
        start_time: number;
        end_time: number;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface State {
        id: number;
        name: string;
        country_id: number;
        country_code: string;
        fips_code: string | null;
        iso2: string | null;
        type: string | null;
        latitude: number | null;
        longitude: number | null;
        created_at: string | null;
        updated_at: string;
        flag: boolean;
        wikiDataId: string | null;
        cities?: Array<App.Models.City> | null;
        cities_count?: number | null;
    }

    export interface Student {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        document_type_id: number | null;
        document: string;
        classroom_user: string | null;
        country_id: number | null;
        city_id: number | null;
        state_id: number | null;
        user_id: number | null;
        created_at: string | null;
        updated_at: string | null;
        lead_id: number | null;
        orders?: Array<App.Models.Order> | null;
        wp_user?: App.Models.Wordpress.WpUser | null;
        wp_learnpress_user_items?: Array<App.Models.Wordpress.WpLearnpressUserItem> | null;
        users?: Array<App.Models.User> | null;
        lead?: App.Models.Lead | null;
        user?: App.Models.User | null;
        document_type?: App.Models.DocumentType | null;
        sap_instalations?: Array<App.Models.SapInstalation> | null;
        city?: App.Models.City | null;
        state?: App.Models.State | null;
        user_assigned?: Array<App.Models.User> | null;
        orders_count?: number | null;
        wp_learnpress_user_items_count?: number | null;
        users_count?: number | null;
        sap_instalations_count?: number | null;
        user_assigned_count?: number | null;
    }

    export interface User {
        id: number;
        name: string;
        email: string;
        email_verified_at: string | null;
        password: string;
        role_id: number | null;
        zadarma_id: string | null;
        remember_token: string | null;
        main_view: string | null;
        active_working: boolean;
        active: boolean;
        created_at: string | null;
        updated_at: string | null;
        photo: string | null;
        zadarma_widget_key: string | null;
        role?: App.Models.Role | null;
        sap_instalation?: Array<App.Models.SapInstalation> | null;
        availability_slots?: Array<App.Models.StaffAvailabilitySlot> | null;
        lead_assignments?: Array<App.Models.LeadAssignment> | null;
        projects?: Array<App.Models.LeadProject> | null;
        projects_pivot?: Array<App.Models.UserLeadProject> | null;
        students?: Array<App.Models.Student> | null;
        zadarma_statistics?: Array<App.Models.ZadarmaStatistic> | null;
        sap_instalation_count?: number | null;
        availability_slots_count?: number | null;
        lead_assignments_count?: number | null;
        projects_count?: number | null;
        projects_pivot_count?: number | null;
        students_count?: number | null;
        zadarma_statistics_count?: number | null;
        readonly unavailable_times?: any;
        readonly bussy_times?: any;
        readonly bussy_times_for_calculate?: any;
    }

    export interface UserLeadProject {
        id: number;
        user_id: number | null;
        lead_project_id: number | null;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface ZadarmaStatistic {
        id: number;
        pbx_call_id: string;
        is_recorded: string;
        seconds: number;
        destination: string;
        clid: string;
        call_id: string;
        disposition: string;
        callstart: string;
        sip: string;
        extension: string;
        created_at: string | null;
        updated_at: string | null;
        user?: App.Models.User | null;
        lead?: App.Models.Lead | null;
    }

    export interface ZohoToken {
        token: string | null;
        type: string | null;
        updated_at: string | null;
    }

}

declare namespace App.Models.Wordpress {
    export interface WpLearnpressUserItem {
        user_item_id: number;
        user_id: number;
        item_id: number;
        start_time: string | null;
        end_time: string | null;
        item_type: string;
        status: string;
        graduation: string | null;
        access_level: string | null;
        ref_id: number;
        ref_type: string | null;
        parent_id: number;
        course?: App.Models.Wordpress.WpPost | null;
        item?: App.Models.Wordpress.WpPost | null;
    }

    export interface WpPost {
        ID: number;
        post_author: number;
        post_date: string;
        post_date_gmt: string;
        post_content: string;
        post_title: string;
        post_excerpt: string;
        post_status: string;
        comment_status: string;
        ping_status: string;
        post_password: string;
        post_name: string;
        to_ping: string;
        pinged: string;
        post_modified: string;
        post_modified_gmt: string;
        post_content_filtered: string;
        post_parent: number;
        guid: string;
        menu_order: number;
        post_type: string;
        post_mime_type: string;
        comment_count: number;
        meta?: Array<App.Models.Wordpress.WpPostMeta> | null;
        meta_count?: number | null;
    }

    export interface WpPostMeta {
        meta_id: number;
        post_id: number;
        meta_key: string | null;
        meta_value: string | null;
    }

    export interface WpUser {
        ID: number;
        user_login: string;
        user_pass: string;
        user_nicename: string;
        user_email: string;
        user_url: string;
        user_registered: string;
        user_activation_key: string;
        user_status: number;
        display_name: string;
    }

}
