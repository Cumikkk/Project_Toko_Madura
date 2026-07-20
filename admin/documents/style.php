<style>
    @page { 
        margin-top: 30px;
        margin-bottom: 30px;
        margin-right: 50px;
        margin-left: 50px;
    }
    body { 
        margin-top: 15px;
        font-family: sans-serif;
        /* border: 1px solid black;  */
    }
    
    .header { 
        display: block;
        margin: auto;
        /* position: fixed;  */
        height: 100px; 
        top: -90px; 
        text-align: center;
        width: 100vw;
        margin-bottom: 20px; 
        /* background-color: purple;  */
    }

    .break-before { 
        page-break-before: always; 
    }
    
    .section { 
        padding: 0px 5px;
    }

    .table {
        width: 100%;
        table-layout: fixed;
        word-break: break-all;
        border-collapse: collapse;
        border: 1px solid black;
    }

    .table th,
    .table td {
        border-collapse: collapse;
        border: 1px solid black;
        padding: 5px;
        text-align: justify;
    }

    .table.no-border,
    .table.no-border td,
    .table.no-border th {
        border: none !important;
    }

    .text-center {
        text-align: center !important;
    }

    .text-justify {
        text-align: justify;
    }

    .v-align-middle {
        vertical-align: middle !important;
    }

    .v-align-top {
        vertical-align: top !important;
    }

    ul.sub-btr {
        counter-reset: item;   
        list-style: none;
    }
    ul.sub-btr > li::before {
        display: inline-block;
        content: "(" counter(item) ")";
        counter-increment: item;
        /* width: 2em; */
        margin-left: -2em;
        margin-top: 3%;
    }
    ul.sub-btr-rum {
        counter-reset: itm;   
        list-style: none;
    }
    ul.sub-btr-rum > li::before {
        display: inline-block;
        content: counter(itm, lower-roman) ").";
        counter-increment: itm;
        /* width: 2em; */
        margin-left: -2em;
        margin-top: 3%;
    }
   
/*     
    #header { 
        position: fixed; 
        left: 0px; 
        top: -15px;
        right: 0px;
        height: 150px;
        bottom: 0px; 
        margin-bottom: 100px;
        text-align: center; 
    }

    .content .page1 {
        margin-top: 140px;
    }
    
    .content .nextpage {
        margin-top: 140px;
        page-break-before: always;
    } */

</style>